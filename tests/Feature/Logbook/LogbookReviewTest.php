<?php

namespace Tests\Feature\Logbook;

use App\Enums\LogbookActivityStatus;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    private function assignInternToPic(User $pic, User $intern, string $startDate = '2026-11-01', ?string $endDate = null): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        DB::table('pic_assignments')->insert([
            'intern_user_id' => $intern->id,
            'pic_user_id' => $pic->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'assigned_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_pic_can_only_view_and_review_logbooks_within_assigned_period(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $unassignedIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // PIC ditugaskan ke intern hanya untuk periode 2026-11-01 s/d 2026-11-18
        $this->assignInternToPic($pic, $intern, '2026-11-01', '2026-11-18');

        $logbookInPeriod = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-15',
            'status' => LogbookStatus::Pending,
        ]);

        $logbookOutsidePeriod = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
        ]);

        $logbookUnassignedIntern = Logbook::factory()->create([
            'intern_user_id' => $unassignedIntern->id,
            'log_date' => '2026-11-15',
            'status' => LogbookStatus::Pending,
        ]);

        // 1. Dalam periode: boleh lihat & boleh review
        $this->actingAs($pic)->get(route('pic.logbooks.show', $logbookInPeriod))->assertOk();
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbookInPeriod), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertRedirect(route('pic.logbooks.show', $logbookInPeriod));

        // 2. Di luar periode (tanggal 20 Nov > 18 Nov): ditolak 403
        $this->actingAs($pic)->get(route('pic.logbooks.show', $logbookOutsidePeriod))->assertForbidden();
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbookOutsidePeriod), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // 3. Peserta yang tidak ditugaskan: ditolak 403
        $this->actingAs($pic)->get(route('pic.logbooks.show', $logbookUnassignedIntern))->assertForbidden();
        $this->actingAs($pic)->post(route('pic.logbooks.review', $logbookUnassignedIntern), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();
    }

    public function test_pic_can_approve_pending_manual_logbook_and_locks_further_intern_edits_or_pic_reviews(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $this->assignInternToPic($pic, $intern);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
            'activity_status' => LogbookActivityStatus::SedangDikerjakan,
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

        // Di halaman detail & daftar PIC, status disetujui hanya menampilkan 1 badge "Disetujui" (tanpa "Sedang Dikerjakan")
        $this->actingAs($intern)
            ->get(route('intern.logbooks.show', $logbook))
            ->assertOk()
            ->assertSee('Disetujui')
            ->assertDontSee('Sedang Dikerjakan');

        $this->actingAs($pic)
            ->get(route('pic.logbooks.index'))
            ->assertOk()
            ->assertSee('Disetujui')
            ->assertDontSee('Sedang Dikerjakan');

        // Setelah approved, peserta tidak bisa mengedit lagi (403 Forbidden)
        $this->actingAs($intern)->get(route('intern.logbooks.edit', $logbook))->assertForbidden();
        $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-20',
            'title' => 'Coba ubah setelah approved',
            'activity_description' => 'Percobaan mengedit logbook yang sudah disetujui PIC.',
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
        $this->assignInternToPic($pic, $intern);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $intern->id,
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
        $this->assertSame('Mohon lengkapi rincian endpoint API yang dikerjakan.', $logbook->review_note);

        // 3. Peserta mengedit logbook -> membuat snapshot di logbook_revisions & status kembali pending
        $this->actingAs($intern)->put(route('intern.logbooks.update', $logbook), [
            'log_date' => '2026-11-20',
            'title' => 'Judul Logbook Setelah Direvisi',
            'activity_description' => 'Mengimplementasikan endpoint POST /pic/logbooks/{id}/review beserta unit test.',
            'result' => 'Endpoint lulus 5 skenario test.',
        ])->assertRedirect(route('intern.logbooks.show', $logbook));

        $logbook->refresh();
        $this->assertSame(LogbookStatus::Pending, $logbook->status);
        $this->assertDatabaseHas('logbook_revisions', [
            'logbook_id' => $logbook->id,
            'revision_no' => 1,
            'title' => 'Judul Awal Logbook',
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
        $this->assignInternToPic($pic, $intern);

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

        $this->assignInternToPic($picOwner, $internOwner);

        $logbook = Logbook::factory()->create([
            'intern_user_id' => $internOwner->id,
            'log_date' => '2026-11-20',
            'status' => LogbookStatus::Pending,
        ]);

        // 1. Peserta pemilik logbook mencoba meng-approve miliknya sendiri -> 403 Forbidden
        $this->actingAs($internOwner)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // 2. Peserta lain -> 403 Forbidden
        $this->actingAs($otherIntern)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // 3. PIC lain (tidak ditugaskan ke peserta ini) -> 403 Forbidden
        $this->actingAs($otherPic)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // 4. Admin/HR (hanya monitoring read-only) -> 403 Forbidden
        $this->actingAs($admin)->post(route('pic.logbooks.review', $logbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->assertSame(LogbookStatus::Pending, $logbook->fresh()->status);
    }

    public function test_multi_pic_related_task_logbook_is_isolated_to_task_owner_pic_while_independent_logbook_is_viewable_by_all_related_pics(): void
    {
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // PIC A adalah PIC pembimbing utama di pic_assignments, sedangkan PIC B hanya memberi tugas di tabel tasks
        $this->assignInternToPic($picA, $intern, '2026-11-01', null);

        $taskPicA = Task::factory()->create([
            'pic_user_id' => $picA->id,
            'intern_user_id' => $intern->id,
            'title' => 'Tugas API dari PIC A',
        ]);

        $taskPicB = Task::factory()->create([
            'pic_user_id' => $picB->id,
            'intern_user_id' => $intern->id,
            'title' => 'Tugas UI dari PIC B',
        ]);

        $taskOtherIntern = Task::factory()->create([
            'pic_user_id' => $picA->id,
            'intern_user_id' => $otherIntern->id,
            'title' => 'Tugas Peserta Lain',
        ]);

        // 1. Peserta ditolak jika memilih related_task_id milik peserta lain
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'related_task_id' => $taskOtherIntern->id,
            'title' => 'Coba Pakai Tugas Orang Lain',
            'activity_description' => 'Mencoba menautkan logbook ke tugas milik peserta lain.',
            'result' => 'Percobaan hasil.',
        ])->assertSessionHasErrors('related_task_id');

        // 2. Peserta membuat logbook terkait tugas PIC B (walaupun PIC B tidak ada di pic_assignments)
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'related_task_id' => $taskPicB->id,
            'title' => 'Mengerjakan Slicing UI Tugas PIC B',
            'activity_description' => 'Membuat komponen tabel untuk tugas yang diberikan PIC B.',
            'result' => 'Komponen tabel tugas PIC B selesai.',
        ])->assertRedirect(route('intern.logbooks.index'));

        $logbookForPicB = Logbook::where('title', 'Mengerjakan Slicing UI Tugas PIC B')->firstOrFail();
        $this->assertSame($taskPicB->id, $logbookForPicB->related_task_id);

        // Logbook terkait tugas PIC B HANYA bisa dilihat dan di-review oleh PIC B (PIC A ditolak 403 meskipun ada di pic_assignments)
        $this->actingAs($picB)->get(route('pic.logbooks.show', $logbookForPicB))->assertOk();
        $this->actingAs($picA)->get(route('pic.logbooks.show', $logbookForPicB))->assertForbidden();
        $this->actingAs($picA)->post(route('pic.logbooks.review', $logbookForPicB), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        // PIC B berhasil me-review logbook terkait tugasnya
        $this->actingAs($picB)->post(route('pic.logbooks.review', $logbookForPicB), [
            'decision' => LogbookStatus::Approved->value,
            'review_note' => 'Slicing UI tugas PIC B sudah sesuai.',
        ])->assertRedirect(route('pic.logbooks.show', $logbookForPicB));
        $this->assertSame(LogbookStatus::Approved, $logbookForPicB->fresh()->status);

        // 3. Peserta membuat logbook mandiri (related_task_id = null)
        $this->actingAs($intern)->post(route('intern.logbooks.store'), [
            'log_date' => '2026-11-20',
            'related_task_id' => null,
            'title' => 'Townhall & Belajar Mandiri',
            'activity_description' => 'Mengikuti pertemuan mingguan perusahaan dan membaca dokumentasi.',
            'result' => 'Ringkasan catatan townhall selesai.',
        ])->assertRedirect(route('intern.logbooks.index'));

        $independentLogbook = Logbook::where('title', 'Townhall & Belajar Mandiri')->firstOrFail();
        $this->assertNull($independentLogbook->related_task_id);

        // Logbook mandiri BISA DILIHAT oleh PIC A (pic_assignments) maupun PIC B (pernah memberi tugas di tasks)
        $this->actingAs($picA)->get(route('pic.logbooks.show', $independentLogbook))->assertOk();
        $this->actingAs($picB)->get(route('pic.logbooks.show', $independentLogbook))->assertOk();

        // Namun yang boleh ME-REVIEW logbook mandiri hanyalah PIC A (pembimbing utama di pic_assignments)
        $this->actingAs($picB)->post(route('pic.logbooks.review', $independentLogbook), [
            'decision' => LogbookStatus::Approved->value,
        ])->assertForbidden();

        $this->actingAs($picA)->post(route('pic.logbooks.review', $independentLogbook), [
            'decision' => LogbookStatus::Approved->value,
            'review_note' => 'Kegiatan mandiri diverifikasi oleh PIC Pembimbing Utama.',
        ])->assertRedirect(route('pic.logbooks.show', $independentLogbook));

        $this->assertSame(LogbookStatus::Approved, $independentLogbook->fresh()->status);
    }
}
