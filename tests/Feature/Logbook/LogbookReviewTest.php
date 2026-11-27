<?php

namespace Tests\Feature\Logbook;

use App\Enums\LogbookActivityStatus;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LogbookReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 10, 0, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pic_can_only_view_and_review_logbooks_targeted_to_them_not_mandiri_or_other_pic(): void
    {
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbookForPicA = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $picA->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
        ]);

        $logbookForPicB = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $picB->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
        ]);

        $mandiriLogbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => null,
            'log_date' => '2026-11-20',
            'status' => null,
        ]);

        // 1. Ditujukan ke PIC A: boleh lihat & boleh review oleh PIC A
        $this->actingAs($picA)->get(route('pic.logbooks.show', $logbookForPicA))->assertOk();
        $this->actingAs($picA)->post(route('pic.logbooks.review', $logbookForPicA), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertRedirect(route('pic.logbooks.show', $logbookForPicA));

        // 2. Ditujukan ke PIC B: ditolak 403 bagi PIC A
        $this->actingAs($picA)->get(route('pic.logbooks.show', $logbookForPicB))->assertForbidden();
        $this->actingAs($picA)->post(route('pic.logbooks.review', $logbookForPicB), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // 3. Logbook Mandiri / Umum (target_pic_user_id = null): ditolak 403 bagi PIC mana pun
        $this->actingAs($picA)->get(route('pic.logbooks.show', $mandiriLogbook))->assertForbidden();
        $this->actingAs($picA)->post(route('pic.logbooks.review', $mandiriLogbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();
    }

    public function test_pic_can_approve_pending_manual_logbook_and_approved_kpi_card_filters_correctly(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-20',
            'title' => 'Integrasi Modul Disetujui PIC',
            'status' => LogbookStatus::Pending,
            'activity_status' => LogbookActivityStatus::Selesai,
        ]);

        // Logbook pending milik PIC ini (untuk memastikan filter ?status=approved mengecualikannya)
        Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-20',
            'title' => 'Logbook Masih Pending',
            'status' => LogbookStatus::Pending,
        ]);

        // Logbook approved milik PIC LAIN (tidak boleh masuk hitungan card Disetujui PIC ini)
        Logbook::factory()->approved($otherPic)->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $otherPic->id,
            'log_date' => '2026-11-20',
            'title' => 'Approved Milik PIC Lain',
        ]);

        $response = $this->actingAs($pic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
            'review_note' => 'Aktivitas harian sudah jelas dan disetujui.',
        ]);

        $response->assertRedirect(route('pic.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame(LogbookStatus::Approved, $logbook->status);
        $this->assertSame(LogbookActivityStatus::Selesai, $logbook->activity_status);
        $this->assertSame($pic->id, $logbook->reviewed_by);
        $this->assertNotNull($logbook->reviewed_at);
        $this->assertSame('Aktivitas harian sudah jelas dan disetujui.', $logbook->review_note);

        // Di halaman detail, status disetujui hanya menampilkan badge "Disetujui" (tanpa "Sedang Dikerjakan")
        $this->actingAs($intern)
            ->get(route('intern.logbooks.show', $logbook))
            ->assertOk()
            ->assertSee('Disetujui')
            ->assertDontSee('Sedang Dikerjakan');

        // Card KPI Disetujui di halaman Logbook PIC menghitung akurat (1) & link ?status=approved memfilter datanya
        $picIndex = $this->actingAs($pic)->get(route('pic.logbooks.index'));
        $picIndex->assertOk();
        $this->assertSame(1, $picIndex->viewData('summary')['approved']);
        $picIndex->assertSee(route('pic.logbooks.index', ['status' => 'approved']), false);
        $picIndex->assertDontSee('Catatan Penugasan');
        $picIndex->assertDontSee('Approved Milik PIC Lain');

        $approvedFilter = $this->actingAs($pic)->get(route('pic.logbooks.index', ['status' => 'approved']));
        $approvedFilter->assertOk();
        $this->assertSame(
            ['Integrasi Modul Disetujui PIC'],
            $approvedFilter->viewData('logbooks')->pluck('title')->all()
        );
        $approvedFilter->assertSee('Integrasi Modul Disetujui PIC');
        $approvedFilter->assertDontSee('Approved Milik PIC Lain');

        // Setelah approved, peserta tidak bisa mengedit lagi (403 Forbidden)
        $this->actingAs($intern)->get(route('intern.logbooks.edit', $logbook))->assertForbidden();
        $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-20',
            'target_pic_user_id' => $pic->id,
            'title' => 'Coba ubah setelah approved',
            'result' => 'Percobaan hasil.',
        ])->assertForbidden();

        // Setelah approved, PIC juga tidak bisa me-review ulang (403 Forbidden)
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::RevisionRequested->value,
            'review_note' => 'Coba ubah keputusan setelah approved.',
        ])->assertForbidden();
    }

    public function test_pic_can_request_revision_with_required_note_intern_revises_creating_snapshot_and_pic_approves(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-20',
            'title' => 'Judul Awal Logbook',
            'activity_description' => 'Uraian aktivitas awal yang masih terlalu singkat.',
            'result' => 'Draft awal',
            'status' => LogbookStatus::Pending,
        ]);

        // 1. Minta revisi TANPA catatan -> wajib ditolak validasi (422)
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::RevisionRequested->value,
            'review_note' => '',
        ])->assertSessionHasErrors('review_note');

        // 2. Minta revisi DENGAN catatan valid -> status menjadi revision_requested
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::RevisionRequested->value,
            'review_note' => 'Mohon lengkapi rincian endpoint API yang dikerjakan.',
        ])->assertRedirect(route('pic.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame(LogbookStatus::RevisionRequested, $logbook->status);

        // 3. Peserta mengedit logbook -> membuat snapshot di logbook_revisions & status kembali pending
        $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-20',
            'target_pic_user_id' => $pic->id,
            'title' => 'Judul Logbook Setelah Direvisi',
            'result' => 'Endpoint lulus 5 skenario test.',
        ])->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame(LogbookStatus::Pending, $logbook->status);
        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'title' => 'Judul Awal Logbook',
            'target_pic_user_id' => $pic->id,
            'status_before' => LogbookStatus::RevisionRequested->value,
            'review_note_before' => 'Mohon lengkapi rincian endpoint API yang dikerjakan.',
            'revised_by' => $intern->id,
        ]);

        // 4. PIC menyetujui ulang logbook yang sudah diperbaiki -> status menjadi approved
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
            'review_note' => 'Perbaikan sudah lengkap dan disetujui.',
        ])->assertRedirect(route('pic.logbooks.show', $logbook));

        $this->assertSame(LogbookStatus::Approved, $logbook->fresh()->status);
    }

    public function test_reviewing_automatic_task_assignment_logbook_is_strictly_rejected(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
        ]);

        $autoLogbook = Logbook::factory()->fromTaskAssignment($task->id, 'Penugasan PIC: Slicing UI')->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-20',
        ]);

        $this->actingAs($pic)->post(route('pic.logbooks.review', $autoLogbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->assertNull($autoLogbook->fresh()->status);
    }

    public function test_rbac_forbids_intern_other_pic_and_admin_from_reviewing_logbook(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picOwner = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internOwner = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $internOwner->id,
            'target_pic_user_id' => $picOwner->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
        ]);

        $this->actingAs($internOwner)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->actingAs($otherIntern)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->actingAs($otherPic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->assertSame(LogbookStatus::Pending, $logbook->fresh()->status);
    }

    public function test_multi_pic_target_selection_and_mandiri_isolation_from_all_pics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $inactivePic = User::factory()->create(['role' => 'pic', 'is_active' => false]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Peserta ditolak jika memilih PIC yang nonaktif atau bukan role PIC
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'target_pic_user_id' => $inactivePic->id,
            'title' => 'Coba Pilih PIC Nonaktif',
            'result' => 'Hasil percobaan.',
        ])->assertSessionHasErrors('target_pic_user_id');

        // 2. Peserta membuat logbook ditujukan ke PIC B -> awalnya Sedang Dikerjakan (status null), belum bisa direview PIC B
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'target_pic_user_id' => $picB->id,
            'title' => 'Mengerjakan Slicing UI Tugas PIC B',
            'result' => 'Komponen tabel tugas PIC B sedang dikerjakan.',
        ])->assertRedirect(route('intern.logbooks.index'));

        $logbookForPicB = Logbook::where('title', 'Mengerjakan Slicing UI Tugas PIC B')->firstOrFail();
        $this->assertSame($picB->id, $logbookForPicB->target_pic_user_id);
        $this->assertSame(LogbookActivityStatus::SedangDikerjakan, $logbookForPicB->activity_status);
        $this->assertNull($logbookForPicB->status);

        // Selama masih Sedang Dikerjakan: belum masuk hitungan Menunggu Review PIC B & aksi review ditolak 403
        $picBIndexBeforeComplete = $this->actingAs($picB)->get(route('pic.logbooks.index'));
        $picBIndexBeforeComplete->assertOk()->assertSee('Mengerjakan Slicing UI Tugas PIC B');
        $this->assertSame(0, $picBIndexBeforeComplete->viewData('summary')['pending']);
        $this->actingAs($picB)->post(route('pic.logbooks.review', $logbookForPicB), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // Setelah peserta menekan Selesai -> otomatis menjadi Pending (Menunggu Review) khusus untuk PIC B
        $this->actingAs($intern)->patch(route('intern.logbooks.complete', $logbookForPicB))->assertRedirect();
        $logbookForPicB->refresh();
        $this->assertSame(LogbookActivityStatus::Selesai, $logbookForPicB->activity_status);
        $this->assertSame(LogbookStatus::Pending, $logbookForPicB->status);

        $picBIndexAfterComplete = $this->actingAs($picB)->get(route('pic.logbooks.index'));
        $picBIndexAfterComplete->assertOk();
        $this->assertSame(1, $picBIndexAfterComplete->viewData('summary')['pending']);

        $this->actingAs($picA)->get(route('pic.logbooks.index'))->assertOk()->assertDontSee('Mengerjakan Slicing UI Tugas PIC B');
        $this->actingAs($picA)->get(route('pic.logbooks.show', $logbookForPicB))->assertForbidden();

        // 3. Peserta membuat logbook Mandiri / Umum (tanpa PIC) -> status null (baik saat Sedang Dikerjakan maupun setelah Selesai)
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'target_pic_user_id' => '',
            'title' => 'Townhall & Belajar Mandiri',
            'result' => 'Ringkasan catatan townhall selesai.',
        ])->assertRedirect(route('intern.logbooks.index'));

        $mandiriLogbook = Logbook::where('title', 'Townhall & Belajar Mandiri')->firstOrFail();
        $this->assertNull($mandiriLogbook->target_pic_user_id);
        $this->assertSame(LogbookActivityStatus::SedangDikerjakan, $mandiriLogbook->activity_status);
        $this->assertNull($mandiriLogbook->status);

        $this->actingAs($intern)->patch(route('intern.logbooks.complete', $mandiriLogbook))->assertRedirect();
        $mandiriLogbook->refresh();
        $this->assertSame(LogbookActivityStatus::Selesai, $mandiriLogbook->activity_status);
        $this->assertNull($mandiriLogbook->status);

        $this->actingAs($intern)->get(route('intern.logbooks.index'))->assertOk()->assertSee('Townhall & Belajar Mandiri');
        $this->actingAs($admin)->get(route('admin.logbooks.index'))->assertOk()->assertSee('Townhall & Belajar Mandiri');
        $this->actingAs($admin)->get(route('admin.logbooks.show', $mandiriLogbook))->assertOk();

        $this->actingAs($picA)->get(route('pic.logbooks.index'))->assertOk()->assertDontSee('Townhall & Belajar Mandiri');
        $this->actingAs($picB)->get(route('pic.logbooks.index'))->assertOk()->assertDontSee('Townhall & Belajar Mandiri');
        $this->actingAs($picA)->get(route('pic.logbooks.show', $mandiriLogbook))->assertForbidden();
        $this->actingAs($picB)->get(route('pic.logbooks.show', $mandiriLogbook))->assertForbidden();

        // EWS logbook harian tetap menghitung keduanya (yang memilih PIC + Mandiri)
        $this->assertSame(2, Logbook::query()->forEwsFulfillment($intern->id, '2026-11-20')->count());
    }
}
