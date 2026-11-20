<?php

namespace Tests\Feature\Logbook;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use App\Services\LogbookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $response1 = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-18',
            'title' => 'Slicing Halaman Logbook',
            'activity_description' => 'Membuat komponen form dan tabel riwayat logbook responsif.',
            'result' => 'Halaman blade selesai dibuat.',
        ]);

        $response1->assertRedirect(route('intern.logbooks.index'));

        $response2 = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-18',
            'title' => 'Integrasi Validasi Form Request',
            'activity_description' => 'Menambahkan validasi batas tanggal mundur 7 hari pada form request.',
            'result' => 'Validasi lulus unit test.',
        ]);

        $response2->assertRedirect(route('intern.logbooks.index'));

        $this->assertDatabaseCount('logbooks', 2);
        $this->assertDatabaseHas('logbooks', [
            'intern_user_id' => $intern->id,
            'title' => 'Slicing Halaman Logbook',
            'source' => LogbookSource::Manual->value,
            'status' => LogbookStatus::Pending->value,
        ]);
    }

    public function test_intern_cannot_create_logbook_for_future_date_or_older_than_max_backdate_days(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Tanggal masa depan (besok)
        $futureResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-19',
            'title' => 'Kegiatan Besok',
            'activity_description' => 'Mencoba mengisi kegiatan untuk besok hari.',
        ]);
        $futureResponse->assertSessionHasErrors('log_date');

        // Lebih dari 7 hari ke belakang (8 hari lalu: 2026-11-10)
        $tooOldResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-10',
            'title' => 'Kegiatan 8 Hari Lalu',
            'activity_description' => 'Mencoba mengisi kegiatan yang sudah lewat batas 7 hari.',
        ]);
        $tooOldResponse->assertSessionHasErrors('log_date');

        // Tepat 7 hari ke belakang (2026-11-11) -> Diizinkan
        $validBackdateResponse = $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-11',
            'title' => 'Kegiatan 7 Hari Lalu',
            'activity_description' => 'Mengisi kegiatan tepat pada batas 7 hari ke belakang.',
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

    public function test_pic_can_only_view_logbooks_within_assigned_period_and_admin_can_view_all(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // PIC A ditugaskan ke intern untuk periode 2026-11-01 s/d 2026-11-15
        DB::table('pic_assignments')->insert([
            'intern_user_id' => $intern->id,
            'pic_user_id' => $picA->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-15',
            'assigned_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $logbookInPeriod = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-12',
        ]);

        $logbookOutsidePeriod = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
        ]);

        // PIC A bisa lihat logbook tanggal 12 Nov, tapi ditolak untuk 18 Nov
        $this->actingAs($picA)
            ->get(route('pic.logbooks.show', $logbookInPeriod))
            ->assertOk();

        $this->actingAs($picA)
            ->get(route('pic.logbooks.show', $logbookOutsidePeriod))
            ->assertForbidden();

        // PIC B (tidak pernah ditugaskan) ditolak keduanya
        $this->actingAs($picB)
            ->get(route('pic.logbooks.show', $logbookInPeriod))
            ->assertForbidden();

        // Admin bisa melihat keduanya
        $this->actingAs($admin)
            ->get(route('admin.logbooks.show', $logbookInPeriod))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.logbooks.show', $logbookOutsidePeriod))
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
        $response->assertSee('+ Tambah Logbook');
        $response->assertSee('Refaktor Komponen Tabel Logbook');
        $response->assertDontSee('Integrasi Endpoint Presensi');
        $response->assertDontSee('Cari peserta...');
    }
}
