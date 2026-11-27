<?php

namespace Tests\Feature\Logbook;

use App\Enums\LogbookActivityStatus;
use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\User;
use App\Services\LogbookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogbookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 18, 9, 0, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_intern_can_create_multiple_manual_logbooks_on_the_same_day(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Logbook dengan PIC tujuan -> dimulai sebagai Sedang Dikerjakan & status null (belum masuk antrian review PIC)
        $response1 = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-18',
            'target_pic_user_id' => $pic->id,
            'title' => 'Slicing Halaman Logbook',
            'activity_description' => 'Membuat komponen form dan tabel riwayat logbook responsif.',
            'result' => 'Halaman blade selesai dibuat.',
        ]);

        $response1->assertRedirect(route('intern.logbooks.index'));

        // 2. Logbook Mandiri / Umum (tanpa PIC) -> dimulai sebagai Sedang Dikerjakan & status null
        $response2 = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-18',
            'target_pic_user_id' => '',
            'title' => 'Integrasi Validasi Form Request',
            'activity_description' => 'Menambahkan validasi batas tanggal mundur 7 hari pada form request.',
            'result' => 'Validasi lulus unit test.',
        ]);

        $response2->assertRedirect(route('intern.logbooks.index'));

        $this->assertDatabaseCount('logbooks', 2);
        $this->assertDatabaseHas('logbooks', [
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'title' => 'Slicing Halaman Logbook',
            'source' => LogbookSource::Manual->value,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan->value,
            'status' => null,
        ]);
        $this->assertDatabaseHas('logbooks', [
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => null,
            'title' => 'Integrasi Validasi Form Request',
            'source' => LogbookSource::Manual->value,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan->value,
            'status' => null,
        ]);

        // EWS harian tetap menghitung KEDUA logbook manual (baik yang memilih PIC maupun Mandiri)
        $this->assertSame(2, Logbook::query()->forEwsFulfillment($intern->id, '2026-11-18')->count());
    }

    public function test_intern_cannot_create_logbook_for_future_date_or_older_than_max_backdate_days(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Tanggal masa depan (besok)
        $futureResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-19',
            'title' => 'Kegiatan Besok',
            'activity_description' => 'Mencoba mengisi kegiatan untuk besok hari.',
            'result' => 'Draft hasil besok',
        ]);
        $futureResponse->assertSessionHasErrors('log_date');

        // Lebih dari 7 hari ke belakang (8 hari lalu: 2026-11-10)
        $tooOldResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-10',
            'title' => 'Kegiatan 8 Hari Lalu',
            'activity_description' => 'Mencoba mengisi kegiatan yang sudah lewat batas 7 hari.',
            'result' => 'Draft hasil 8 hari lalu',
        ]);
        $tooOldResponse->assertSessionHasErrors('log_date');

        // Tepat 7 hari ke belakang (2026-11-11) -> Diizinkan
        $validBackdateResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-11',
            'title' => 'Kegiatan 7 Hari Lalu',
            'activity_description' => 'Mengisi kegiatan tepat pada batas 7 hari ke belakang.',
            'result' => 'Hasil 7 hari lalu selesai',
        ]);
        $validBackdateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseCount('logbooks', 1);
    }

    public function test_intern_cannot_view_or_update_another_interns_logbook(): void
    {
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbookA = Logbook::factory()->create([
            'intern_user_id' => $internA->id,
            'log_date' => '2026-11-18',
            'status' => LogbookStatus::Pending,
        ]);

        $this->actingAs($internB)
            ->get(route('intern.logbooks.show', $logbookA))
            ->assertForbidden();

        $this->actingAs($internB)
            ->get(route('intern.logbooks.edit', $logbookA))
            ->assertForbidden();

        $this->actingAs($internB)
            ->put(route('intern.logbooks.update', $logbookA), [
                'log_date' => '2026-11-18',
                'title' => 'Diubah oleh peserta lain',
                'activity_description' => 'Percobaan mengubah data milik peserta lain.',
            ])
            ->assertForbidden();
    }

    public function test_updating_logbook_creates_revision_snapshot_and_resets_revision_requested_to_pending(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $logbook = Logbook::factory()->revisionRequested($pic, 'Tolong perjelas metrik pengujian.')->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-17',
            'title' => 'Judul Awal',
            'activity_description' => 'Deskripsi awal sebelum direvisi peserta.',
            'result' => 'Hasil awal',
        ]);

        $response = $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-17',
            'title' => 'Judul Setelah Revisi',
            'activity_description' => 'Deskripsi lengkap setelah diperbaiki sesuai catatan PIC.',
            'result' => 'Lulus 12 skenario pengujian',
        ]);

        $response->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame('Judul Setelah Revisi', $logbook->title);
        $this->assertSame(LogbookStatus::Pending, $logbook->status);

        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'title' => 'Judul Awal',
            'activity_description' => 'Deskripsi awal sebelum direvisi peserta.',
            'status_before' => LogbookStatus::RevisionRequested->value,
            'review_note_before' => 'Tolong perjelas metrik pengujian.',
            'revised_by' => $intern->id,
        ]);
    }

    public function test_intern_cannot_edit_approved_or_task_assignment_logbook(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $approvedLogbook = Logbook::factory()->approved()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
        ]);

        $task = Task::factory()->create(['intern_user_id' => $intern->id]);

        $autoLogbook = Logbook::factory()->fromTaskAssignment($task->id, 'Tugas Integrasi API')->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
        ]);

        $this->actingAs($intern)
            ->get(route('intern.logbooks.edit', $approvedLogbook))
            ->assertForbidden();

        $this->actingAs($intern)
            ->put(route('intern.logbooks.update', $approvedLogbook), [
                'log_date' => '2026-11-18',
                'title' => 'Coba ubah yang sudah approved',
                'activity_description' => 'Deskripsi percobaan ubah logbook approved.',
            ])
            ->assertForbidden();

        $this->actingAs($intern)
            ->put(route('intern.logbooks.update', $autoLogbook), [
                'log_date' => '2026-11-18',
                'title' => 'Coba ubah logbook otomatis',
                'activity_description' => 'Deskripsi percobaan ubah logbook otomatis.',
            ])
            ->assertForbidden();
    }

    public function test_pic_can_only_view_logbooks_targeted_to_them_and_admin_can_view_all_including_mandiri(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbookForPicA = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $picA->id,
            'log_date' => '2026-11-12',
            'status' => LogbookStatus::Pending,
        ]);

        $mandiriLogbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => null,
            'log_date' => '2026-11-18',
            'status' => null,
        ]);

        // PIC A bisa melihat logbook yang ditujukan ke PIC A, tapi ditolak untuk logbook Mandiri (tanpa PIC)
        $this->actingAs($picA)
            ->get(route('pic.logbooks.show', $logbookForPicA))
            ->assertOk();

        $this->actingAs($picA)
            ->get(route('pic.logbooks.show', $mandiriLogbook))
            ->assertForbidden();

        // PIC B ditolak keduanya
        $this->actingAs($picB)
            ->get(route('pic.logbooks.show', $logbookForPicA))
            ->assertForbidden();

        $this->actingAs($picB)
            ->get(route('pic.logbooks.show', $mandiriLogbook))
            ->assertForbidden();

        // Peserta pemilik & Admin bisa melihat keduanya (termasuk Mandiri)
        $this->actingAs($intern)
            ->get(route('intern.logbooks.show', $mandiriLogbook))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.logbooks.show', $logbookForPicA))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.logbooks.show', $mandiriLogbook))
            ->assertOk();
    }

    public function test_automatic_task_assignment_logbook_is_idempotent_and_excluded_from_approval_and_ews(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $task = Task::factory()->create(['intern_user_id' => $intern->id]);
        $service = app(LogbookService::class);

        $first = $service->recordTaskAssignmentEntry(
            internUserId: $intern->id,
            taskId: $task->id,
            taskTitle: 'Penugasan: Desain Skema Database',
            activityDescription: 'PIC memberikan tugas Desain Skema Database.',
            logDate: '2026-11-18'
        );

        // Pemanggilan ulang dengan task_id yang sama tidak boleh menduplikasi baris
        $second = $service->recordTaskAssignmentEntry(
            internUserId: $intern->id,
            taskId: $task->id,
            taskTitle: 'Penugasan: Desain Skema Database (Refresh)',
            activityDescription: 'PIC memberikan tugas Desain Skema Database.',
            logDate: '2026-11-18'
        );

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('logbooks', 1);
        $this->assertNull($first->status);

        // Tidak masuk antrian approval & tidak dihitung sebagai pemenuhan EWS harian
        $this->assertSame(0, Logbook::query()->pendingApproval()->count());
        $this->assertSame(0, Logbook::query()->forEwsFulfillment($intern->id, '2026-11-18')->count());
    }

    public function test_logbook_index_supports_keyword_search_pagination_and_hides_global_intern_search_for_intern(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
            'title' => 'Refaktor Komponen Tabel Logbook',
            'activity_description' => 'Mengubah layout halaman riwayat menjadi full-width.',
        ]);

        Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-17',
            'title' => 'Integrasi Endpoint Presensi',
            'activity_description' => 'Menyiapkan validasi jam masuk.',
        ]);

        $response = $this->actingAs($intern)->get(route('intern.logbooks.index', [
            'search' => 'Refaktor Komponen',
        ]));

        $response->assertOk();
        $response->assertSee('Tambah Logbook');
        $response->assertSee('Refaktor Komponen Tabel Logbook');
        $response->assertDontSee('Integrasi Endpoint Presensi');
        $response->assertDontSee('Cari peserta...');
    }

    public function test_intern_can_create_and_update_logbook_without_activity_description_and_it_autofills_from_title(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $storeResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            '_modal' => 'create',
            'log_date' => '2026-11-18',
            'title' => 'Menyusun dokumentasi endpoint presensi',
            'result' => 'Dokumentasi selesai direview',
        ]);

        $storeResponse->assertRedirect(route('intern.logbooks.index'));
        $storeResponse->assertSessionHasNoErrors();

        $logbook = Logbook::query()->where('intern_user_id', $intern->id)->firstOrFail();
        $this->assertSame('Menyusun dokumentasi endpoint presensi', $logbook->title);
        $this->assertSame('Menyusun dokumentasi endpoint presensi', $logbook->activity_description);

        $updateResponse = $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-18',
            'title' => 'Menyusun dokumentasi endpoint presensi v2',
            'result' => 'Revisi dokumentasi selesai',
        ]);

        $updateResponse->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame('Menyusun dokumentasi endpoint presensi v2', $logbook->activity_description);
        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'title' => 'Menyusun dokumentasi endpoint presensi',
            'activity_description' => 'Menyusun dokumentasi endpoint presensi',
        ]);

        $showResponse = $this->actingAs($intern)->get(route('intern.logbooks.show', $logbook));
        $showResponse->assertOk();
        $showResponse->assertSee('Menyusun dokumentasi endpoint presensi v2');
        $showResponse->assertDontSee('Uraian Aktivitas');
        $showResponse->assertDontSee('WIB');
    }

    public function test_logbook_index_renders_create_modal_and_reopens_on_validation_error(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $indexResponse = $this->actingAs($intern)->get(route('intern.logbooks.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('id="create-logbook-modal"', false);
        $indexResponse->assertSee('data-open-on-load="false"', false);
        $indexResponse->assertSee('Kegiatan');
        $indexResponse->assertDontSee('WIB');

        $invalidResponse = $this->actingAs($intern)
            ->from(route('intern.logbooks.index'))
            ->followingRedirects()
            ->post(route('intern.logbooks.store'), [
                '_modal' => 'create',
                'log_date' => '2026-11-18',
                'title' => '',
                'result' => 'Catatan sementara',
            ]);

        $invalidResponse->assertOk();
        $invalidResponse->assertSee('data-open-on-load="true"', false);
        $invalidResponse->assertSee('Kegiatan wajib diisi.');
        $invalidResponse->assertSee('Catatan sementara');
        $invalidResponse->assertDontSee('Terkait Tugas PIC');
    }

    public function test_manual_logbook_requires_result_text_or_evidence_screenshot(): void
    {
        Storage::fake('local_private');
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Tanpa result DAN tanpa evidence_file -> ditolak (422 / session errors)
        $this->actingAs($intern)
            ->postJson(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Kegiatan tanpa hasil sama sekali',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['result', 'evidence_file']);

        // 2. Hanya dengan teks result -> berhasil
        $this->actingAs($intern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Kegiatan dengan teks hasil saja',
                'result' => 'Dokumentasi API selesai disusun.',
            ])
            ->assertRedirect(route('intern.logbooks.index'))
            ->assertSessionHasNoErrors();

        // 3. Hanya dengan screenshot evidence_file -> berhasil
        $this->actingAs($intern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Kegiatan dengan screenshot saja',
                'evidence_file' => UploadedFile::fake()->image('bukti-kerja.png', 800, 600),
            ])
            ->assertRedirect(route('intern.logbooks.index'))
            ->assertSessionHasNoErrors();

        // 4. Dengan keduanya (result + evidence_file) -> berhasil
        $this->actingAs($intern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Kegiatan dengan teks dan screenshot',
                'result' => 'PR #45 sudah di-merge.',
                'evidence_file' => UploadedFile::fake()->image('pr-merged.jpg', 640, 480),
            ])
            ->assertRedirect(route('intern.logbooks.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('logbooks', 3);
    }

    public function test_logbook_evidence_file_validation_private_storage_and_append_only_revision_history(): void
    {
        Storage::fake('local_private');
        Storage::fake('public');

        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. File non-gambar (PDF) -> ditolak 422
        $this->actingAs($intern)
            ->postJson(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Upload file bukan gambar',
                'evidence_file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_file']);

        // 2. File gambar melebihi batas 5 MB (6000 KB) -> ditolak 422
        $this->actingAs($intern)
            ->postJson(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Upload gambar terlalu besar',
                'evidence_file' => UploadedFile::fake()->image('terlalu-besar.png')->size(6000),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_file']);

        // 3. File gambar valid tersimpan di local_private (bukan public) via MediaStorageService
        $this->actingAs($intern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Slicing modal logbook',
                'evidence_file' => UploadedFile::fake()->image('bukti-v1.png', 640, 480),
            ])
            ->assertRedirect(route('intern.logbooks.index'));

        $logbook = Logbook::query()->where('title', 'Slicing modal logbook')->firstOrFail();
        $this->assertNotNull($logbook->evidence_media_id);

        $firstMedia = MediaFile::findOrFail($logbook->evidence_media_id);
        $this->assertSame('local_private', $firstMedia->disk);
        $this->assertSame('logbook_evidence', $firstMedia->category);
        Storage::disk('local_private')->assertExists($firstMedia->file_path);
        Storage::disk('public')->assertMissing($firstMedia->file_path);

        // 4. Edit dengan screenshot baru: bukti lama TIDAK dihapus dan tersimpan di logbook_revisions
        $this->actingAs($intern)
            ->put(route('intern.logbooks.update', $logbook), [
                'log_date' => '2026-11-18',
                'title' => 'Slicing modal logbook v2',
                'evidence_file' => UploadedFile::fake()->image('bukti-v2.png', 640, 480),
            ])
            ->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertNotSame($firstMedia->id, $logbook->evidence_media_id);
        $this->assertDatabaseHas('media_files', ['id' => $firstMedia->id]);
        Storage::disk('local_private')->assertExists($firstMedia->file_path);
        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'evidence_media_id' => $firstMedia->id,
        ]);
    }

    public function test_logbook_evidence_private_media_authorization_allows_owner_authorized_pic_and_admin_only(): void
    {
        Storage::fake('local_private');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $authorizedPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $unauthorizedPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $ownerIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        DB::table('pic_assignments')->insert([
            'intern_user_id' => $ownerIntern->id,
            'pic_user_id' => $authorizedPic->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
            'assigned_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($ownerIntern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'target_pic_user_id' => $authorizedPic->id,
                'title' => 'Bukti privat peserta',
                'evidence_file' => UploadedFile::fake()->image('privat.png', 400, 300),
            ])
            ->assertRedirect(route('intern.logbooks.index'));

        $logbook = Logbook::query()->where('intern_user_id', $ownerIntern->id)->firstOrFail();
        $media = MediaFile::findOrFail($logbook->evidence_media_id);

        // Diizinkan: pemilik, PIC terkait, Admin
        $this->actingAs($ownerIntern)->get(route('media.private.show', $media))->assertOk();
        $this->actingAs($authorizedPic)->get(route('media.private.show', $media))->assertOk();
        $this->actingAs($admin)->get(route('media.private.show', $media))->assertOk();

        // Ditolak: peserta lain dan PIC lain
        $this->actingAs($otherIntern)->get(route('media.private.show', $media))->assertForbidden();
        $this->actingAs($unauthorizedPic)->get(route('media.private.show', $media))->assertForbidden();
    }

    public function test_task_assignment_logbook_displays_realtime_task_status_while_keeping_db_status_null(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Integrasi Modul Presensi',
            'status' => TaskStatus::Assigned,
        ]);

        $autoLogbook = Logbook::factory()->fromTaskAssignment($task->id, 'Penugasan PIC: Integrasi Modul Presensi')->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
        ]);

        foreach ([
            TaskStatus::Assigned,
            TaskStatus::InProgress,
            TaskStatus::Submitted,
            TaskStatus::RevisionRequested,
            TaskStatus::Accepted,
        ] as $status) {
            $task->update(['status' => $status]);

            $response = $this->actingAs($intern)->get(route('intern.logbooks.index'));
            $response->assertOk();
            $response->assertSee('Otomatis Tugas');
            $response->assertSee($status->label());
        }

        // Di level DB, status logbook task_assignment tetap null & dikecualikan dari approval serta EWS
        $this->assertNull($autoLogbook->fresh()->status);
        $this->assertSame(0, Logbook::query()->pendingApproval()->count());
        $this->assertSame(0, Logbook::query()->forEwsFulfillment($intern->id, '2026-11-18')->count());
    }

    public function test_manual_logbook_activity_status_dropdown_and_automatic_baru_dikasih_on_task_assignment(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $task = Task::factory()->create(['intern_user_id' => $intern->id]);

        // 1. Nilai tidak valid -> ditolak 422
        $this->actingAs($intern)
            ->postJson(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'title' => 'Uji status tidak valid',
                'result' => 'Hasil uji',
                'activity_status' => 'ngawur',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('activity_status');

        // 2. Simpan dengan status "sedang_dikerjakan" lalu edit menjadi "selesai" -> snapshot menyimpan status lama
        $this->actingAs($intern)
            ->post(route('intern.logbooks.store'), [
                'log_date' => '2026-11-18',
                'target_pic_user_id' => $pic->id,
                'title' => 'Integrasi dropdown status',
                'result' => 'Progres 50%',
                'activity_status' => LogbookActivityStatus::SedangDikerjakan->value,
            ])
            ->assertRedirect(route('intern.logbooks.index'));

        $logbook = Logbook::query()->where('title', 'Integrasi dropdown status')->firstOrFail();
        $this->assertSame(LogbookActivityStatus::SedangDikerjakan, $logbook->activity_status);
        $this->assertNull($logbook->status);

        $this->actingAs($intern)
            ->put(route('intern.logbooks.update', $logbook), [
                'log_date' => '2026-11-18',
                'target_pic_user_id' => $pic->id,
                'title' => 'Integrasi dropdown status',
                'result' => 'Progres 100%',
                'activity_status' => LogbookActivityStatus::Selesai->value,
            ])
            ->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame(LogbookActivityStatus::Selesai, $logbook->activity_status);
        $this->assertSame(LogbookStatus::Pending, $logbook->status);
        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan->value,
        ]);

        // 3. Logbook otomatis dari penugasan PIC otomatis mendapat activity_status = baru_dikasih
        $auto = app(LogbookService::class)->recordTaskAssignmentEntry(
            internUserId: $intern->id,
            taskId: $task->id,
            taskTitle: 'Penugasan PIC: Tugas Baru',
            activityDescription: 'PIC memberikan tugas baru.',
            logDate: '2026-11-18'
        );
        $this->assertSame(LogbookActivityStatus::BaruDikasih, $auto->activity_status);
        $this->assertNull($auto->status);
    }

    public function test_intern_logbook_index_groups_entries_by_day_and_shows_all_entries_per_day_without_inner_pagination(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 7 entri pada 2026-11-18 (hari ke-1) dan 7 entri pada 2026-11-17 (hari ke-2)
        for ($i = 1; $i <= 7; $i++) {
            Logbook::factory()->create([
                'intern_user_id' => $intern->id,
                'log_date' => '2026-11-18',
                'title' => "Kegiatan Hari 18 #{$i}",
                'created_at' => Carbon::create(2026, 11, 18, 8, $i, 0, 'Asia/Jakarta'),
            ]);
            Logbook::factory()->create([
                'intern_user_id' => $intern->id,
                'log_date' => '2026-11-17',
                'title' => "Kegiatan Hari 17 #{$i}",
                'created_at' => Carbon::create(2026, 11, 17, 8, $i, 0, 'Asia/Jakarta'),
            ]);
        }

        // Seluruh 7 entri di masing-masing tanggal tampil utuh dalam 1 kartu hari (tanpa pagination per-kartu)
        $response = $this->actingAs($intern)->get(route('intern.logbooks.index'));
        $response->assertOk();
        $response->assertSee('data-logbook-day="2026-11-18"', false);
        $response->assertSee('data-logbook-day="2026-11-17"', false);
        $response->assertSee('Kegiatan Hari 18 #7');
        $response->assertSee('Kegiatan Hari 18 #1');
        $response->assertSee('Kegiatan Hari 17 #7');
        $response->assertSee('Kegiatan Hari 17 #1');
        $response->assertDontSee('page_2026_11_18', false);
        $response->assertDontSee('page_2026_11_17', false);
    }

    public function test_intern_inter_day_pagination_preserves_filter_query_string(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Buat 7 hari berbeda (12 Nov s/d 18 Nov) dengan kata kunci "Progres"
        foreach (range(12, 18) as $day) {
            $date = sprintf('2026-11-%02d', $day);
            Logbook::factory()->create([
                'intern_user_id' => $intern->id,
                'log_date' => $date,
                'title' => "Progres Harian Tanggal {$day}",
            ]);
        }

        // Halaman 1 antar-hari (page=1): 5 hari terbaru (18, 17, 16, 15, 14)
        $datesPage1 = $this->actingAs($intern)->get(route('intern.logbooks.index', [
            'search' => 'Progres',
        ]));
        $datesPage1->assertOk();
        $datesPage1->assertSee('data-logbook-day="2026-11-18"', false);
        $datesPage1->assertSee('data-logbook-day="2026-11-14"', false);
        $datesPage1->assertDontSee('data-logbook-day="2026-11-13"', false);
        $datesPage1->assertSee('page=2', false);
        $datesPage1->assertSee('search=Progres', false);

        // Halaman 2 antar-hari (page=2): 2 hari tersisa (13, 12) sambil mempertahankan filter search
        $datesPage2 = $this->actingAs($intern)->get(route('intern.logbooks.index', [
            'search' => 'Progres',
            'page' => 2,
        ]));
        $datesPage2->assertOk();
        $datesPage2->assertSee('data-logbook-day="2026-11-13"', false);
        $datesPage2->assertSee('data-logbook-day="2026-11-12"', false);
        $datesPage2->assertDontSee('data-logbook-day="2026-11-18"', false);
    }

    public function test_intern_dates_pagination_counts_distinct_days_not_raw_rows_and_clamps_out_of_range_page(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 9 baris logbook yang tersebar hanya di 3 tanggal berbeda (3 baris per tanggal)
        foreach (['2026-11-18', '2026-11-17', '2026-11-16'] as $date) {
            for ($i = 1; $i <= 3; $i++) {
                Logbook::factory()->create([
                    'intern_user_id' => $intern->id,
                    'log_date' => $date,
                    'title' => "Catatan {$date} #{$i}",
                ]);
            }
        }

        $response = $this->actingAs($intern)->get(route('intern.logbooks.index'));
        $response->assertOk();

        // Total paginator tanggal harus 3 (jumlah hari unik), BUKAN 9 (jumlah baris logbook)
        $paginatedDates = $response->viewData('paginatedDates');
        $this->assertSame(3, $paginatedDates->total());
        $this->assertSame(1, $paginatedDates->lastPage());
        $response->assertDontSee('page=2', false);

        // Mengakses page=99 (melebihi halaman terakhir) tetap menampilkan halaman terakhir yang valid, bukan kosong
        $outOfRange = $this->actingAs($intern)->get(route('intern.logbooks.index', ['page' => 99]));
        $outOfRange->assertOk();
        $outOfRange->assertSee('data-logbook-day="2026-11-18"', false);
        $outOfRange->assertDontSee('Belum ada catatan logbook');

        // Tambah 3 tanggal lagi (total 6 tanggal unik -> 2 halaman dengan 5 hari/halaman)
        foreach (['2026-11-15', '2026-11-14', '2026-11-13'] as $date) {
            Logbook::factory()->create([
                'intern_user_id' => $intern->id,
                'log_date' => $date,
                'title' => "Catatan {$date}",
            ]);
        }

        $page2 = $this->actingAs($intern)->get(route('intern.logbooks.index', ['page' => 2]));
        $page2->assertOk();
        $this->assertSame(6, $page2->viewData('paginatedDates')->total());
        $page2->assertSee('data-logbook-day="2026-11-13"', false);
        $page2->assertSee('page=1', false);

        // Mengakses page=99 saat ada 2 halaman -> otomatis menampilkan halaman 2 beserta link navigasi
        $clampedPage2 = $this->actingAs($intern)->get(route('intern.logbooks.index', ['page' => 99]));
        $clampedPage2->assertOk();
        $clampedPage2->assertSee('data-logbook-day="2026-11-13"', false);
        $clampedPage2->assertSee('page=1', false);
        $clampedPage2->assertDontSee('Belum ada catatan logbook');
    }

    public function test_completing_manual_logbook_transitions_pic_targeted_to_pending_and_keeps_mandiri_null(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $picTargetedInProgress = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-18',
            'title' => 'Integrasi halaman dashboard PIC',
            'status' => null,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan,
        ]);

        $mandiriInProgress = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => null,
            'log_date' => '2026-11-18',
            'title' => 'Belajar dokumentasi Laravel Mandiri',
            'status' => null,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan,
        ]);

        $approvedLogbook = Logbook::factory()->approved()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-18',
            'activity_status' => LogbookActivityStatus::Selesai,
        ]);

        $task = Task::factory()->create(['pic_user_id' => $pic->id, 'intern_user_id' => $intern->id]);
        $autoLogbook = Logbook::factory()->fromTaskAssignment($task->id, 'Penugasan PIC: API')->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
            'activity_status' => LogbookActivityStatus::BaruDikasih,
        ]);

        // 1. Peserta lain & PIC ditolak (403)
        $this->actingAs($otherIntern)->patch(route('intern.logbooks.complete', $picTargetedInProgress))->assertForbidden();
        $this->actingAs($pic)->patch(route('intern.logbooks.complete', $picTargetedInProgress))->assertForbidden();

        // 2. Logbook approved & logbook task_assignment ditolak (403)
        $this->actingAs($intern)->patch(route('intern.logbooks.complete', $approvedLogbook))->assertForbidden();
        $this->actingAs($intern)->patch(route('intern.logbooks.complete', $autoLogbook))->assertForbidden();

        // 3. Klik Selesai pada logbook bertarget PIC -> activity_status Selesai & status Pending (Menunggu Review)
        $this->actingAs($intern)
            ->from(route('intern.logbooks.index'))
            ->patch(route('intern.logbooks.complete', $picTargetedInProgress))
            ->assertRedirect(route('intern.logbooks.index'))
            ->assertSessionHas('success_title', 'Kegiatan Selesai');

        $picTargetedInProgress->refresh();
        $this->assertSame(LogbookActivityStatus::Selesai, $picTargetedInProgress->activity_status);
        $this->assertSame(LogbookStatus::Pending, $picTargetedInProgress->status);
        $this->assertSame('Menunggu Review', $picTargetedInProgress->displayStatusBadge()['label']);

        // 4. Klik Selesai pada logbook Mandiri -> activity_status Selesai & status tetap null (tidak pernah Menunggu Review)
        $this->actingAs($intern)
            ->from(route('intern.logbooks.index'))
            ->patch(route('intern.logbooks.complete', $mandiriInProgress))
            ->assertRedirect(route('intern.logbooks.index'));

        $mandiriInProgress->refresh();
        $this->assertSame(LogbookActivityStatus::Selesai, $mandiriInProgress->activity_status);
        $this->assertNull($mandiriInProgress->status);
        $this->assertSame('Selesai', $mandiriInProgress->displayStatusBadge()['label']);

        // 5. Mengubah target PIC pada logbook Selesai: Mandiri -> PIC menjadi Pending, PIC -> Mandiri menjadi null
        $this->actingAs($intern)->put(route('intern.logbooks.update', $mandiriInProgress), [
            'log_date' => '2026-11-18',
            'target_pic_user_id' => $pic->id,
            'title' => 'Belajar dokumentasi Laravel Mandiri',
            'result' => 'Ringkasan selesai dan diajukan ke PIC.',
        ])->assertRedirect(route('intern.logbooks.show', $mandiriInProgress));

        $this->assertSame(LogbookStatus::Pending, $mandiriInProgress->fresh()->status);

        $this->actingAs($intern)->put(route('intern.logbooks.update', $mandiriInProgress), [
            'log_date' => '2026-11-18',
            'target_pic_user_id' => '',
            'title' => 'Belajar dokumentasi Laravel Mandiri',
            'result' => 'Dikembalikan menjadi catatan mandiri.',
        ])->assertRedirect(route('intern.logbooks.show', $mandiriInProgress));

        $this->assertNull($mandiriInProgress->fresh()->status);

        // 6. Tidak bisa ditandai Selesai dua kali (403)
        $this->actingAs($intern)->patch(route('intern.logbooks.complete', $picTargetedInProgress))->assertForbidden();
    }
}
