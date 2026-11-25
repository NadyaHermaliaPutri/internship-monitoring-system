<?php

namespace Tests\Feature\Task;

use App\Enums\TaskReviewDecision;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 9, 30, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pic_owner_can_accept_submitted_task_sets_accepted_metadata_and_creates_review_row(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        // Peserta mengumpulkan tugas
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/21',
            'submission_note' => 'Sudah siap ditinjau PIC.',
        ])->assertRedirect(route('intern.tasks.show', $task));

        $submission = TaskSubmission::firstOrFail();

        // PIC menerima tugas (accepted)
        $response = $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $submission->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Pekerjaan rapi dan sesuai spesifikasi.',
        ]);

        $response->assertRedirect(route('pic.tasks.show', $task));

        $task->refresh();
        $this->assertSame(TaskStatus::Accepted, $task->status);
        $this->assertNotNull($task->accepted_at);
        $this->assertSame($pic->id, $task->accepted_by);

        $this->assertDatabaseCount('task_reviews', 1);
        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $submission->id,
            'reviewed_by' => $pic->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Pekerjaan rapi dan sesuai spesifikasi.',
        ]);
    }

    public function test_full_revision_cycle_preserves_all_submission_and_review_rows_without_overwriting(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::InProgress,
        ]);

        // 1. Peserta submit ke-1 -> status submitted
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/commit/v1',
            'submission_note' => 'Pengumpulan versi pertama.',
        ]);

        $sub1 = TaskSubmission::where('task_id', $task->id)->where('submission_number', 1)->firstOrFail();

        // 2. PIC minta revisi (revision_requested) pada sub1 -> baris ke-1 di task_reviews
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $sub1->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Mohon perbaiki tampilan tabel pada layar mobile.',
        ])->assertRedirect(route('pic.tasks.show', $task));

        $this->assertSame(TaskStatus::RevisionRequested, $task->fresh()->status);

        // 3. Peserta resubmit (submission_number = 2) -> status resubmitted
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/commit/v2',
            'submission_note' => 'Revisi tampilan mobile sudah diperbaiki.',
        ]);

        $this->assertSame(TaskStatus::Resubmitted, $task->fresh()->status);
        $sub2 = TaskSubmission::where('task_id', $task->id)->where('submission_number', 2)->firstOrFail();

        // 4. PIC terima (accepted) pada sub2 -> baris ke-2 di task_reviews
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $sub2->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Revisi sudah sesuai dan diterima.',
        ])->assertRedirect(route('pic.tasks.show', $task));

        $this->assertSame(TaskStatus::Accepted, $task->fresh()->status);

        // Pastikan 2 baris di task_submissions dan 2 baris di task_reviews tetap utuh
        $this->assertDatabaseCount('task_submissions', 2);
        $this->assertDatabaseCount('task_reviews', 2);

        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $sub1->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Mohon perbaiki tampilan tabel pada layar mobile.',
        ]);

        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $sub2->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Revisi sudah sesuai dan diterima.',
        ]);
    }

    public function test_rejects_revision_without_feedback_or_review_on_invalid_task_status(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        // 1. Mencoba review saat status masih assigned (belum ada submission) -> error validasi status
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertSessionHasErrors('status');

        // 2. Mencoba review saat status in_progress -> error validasi status
        $task->update(['status' => TaskStatus::InProgress]);
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertSessionHasErrors('status');

        // Peserta submit tugas -> status submitted
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/50',
        ]);

        // 3. PIC memilih revision_requested TANPA feedback -> wajib ditolak
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => '',
        ])->assertSessionHasErrors('feedback');

        // 4. PIC meminta revisi dengan feedback valid -> status jadi revision_requested
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Tambahkan pengujian unit test.',
        ])->assertSessionHasNoErrors();

        // 5. Mencoba review lagi saat status masih revision_requested (peserta belum resubmit) -> ditolak
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseCount('task_reviews', 1);
    }

    public function test_accepted_task_is_final_not_overdue_and_locked_from_edit_resubmit_or_re_review(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'deadline_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'status' => TaskStatus::Submitted,
        ]);

        TaskSubmission::create([
            'task_id' => $task->id,
            'submitted_by' => $intern->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/ptwan/repo',
            'submitted_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // PIC menerima tugas
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertRedirect(route('pic.tasks.show', $task));

        // Majukan waktu melewati deadline (3 hari kemudian)
        Carbon::setTestNow(Carbon::create(2026, 11, 23, 10, 0, 0, 'Asia/Jakarta'));

        $task->refresh();
        $this->assertFalse($task->isOverdue());
        $this->assertSame(0, Task::query()->overdue()->count());

        // Tidak bisa diedit oleh PIC
        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Ubah setelah accepted',
            'description' => 'Deskripsi ubah setelah accepted.',
            'deadline_at' => '2026-11-30 17:00:00',
        ])->assertForbidden();

        // Tidak bisa disubmit ulang oleh peserta
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/v2',
        ])->assertForbidden();

        // Tidak bisa direview ulang oleh PIC
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Coba ubah keputusan setelah accepted.',
        ])->assertSessionHasErrors('status');
    }

    public function test_rbac_forbids_intern_other_pic_and_admin_from_reviewing_task(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picOwner = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internOwner = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $picOwner->id,
            'intern_user_id' => $internOwner->id,
            'status' => TaskStatus::Submitted,
        ]);

        TaskSubmission::create([
            'task_id' => $task->id,
            'submitted_by' => $internOwner->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/ptwan/repo',
            'submitted_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // 1. Peserta pemilik tugas mencoba me-review / meng-accept tugasnya sendiri -> 403 Forbidden
        $this->actingAs($internOwner)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        // 2. Peserta lain -> 403 Forbidden
        $this->actingAs($otherIntern)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        // 3. PIC lain (bukan penanggung jawab tugas) -> 403 Forbidden
        $this->actingAs($otherPic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        // 4. Admin (hanya monitoring read-only) -> 403 Forbidden
        $this->actingAs($admin)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        $this->assertDatabaseCount('task_reviews', 0);
        $this->assertSame(TaskStatus::Submitted, $task->fresh()->status);
    }
}
