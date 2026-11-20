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

class TaskStatusTransitionTest extends TestCase
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

    public function test_state_machine_matrix_in_enum_and_model_enforces_exact_allowed_transitions_per_role(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        // Dari Assigned: Intern boleh ke InProgress atau Submitted (Opsi A fleksibel), tidak boleh ke Accepted/RevisionRequested
        $this->assertTrue($task->canTransitionTo(TaskStatus::InProgress, $intern));
        $this->assertTrue($task->canTransitionTo(TaskStatus::Submitted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::RevisionRequested, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Resubmitted, $intern));

        // Dari Assigned: PIC dan Admin tidak boleh melompat ke status mana pun
        foreach (TaskStatus::cases() as $target) {
            $this->assertFalse($task->canTransitionTo($target, $pic));
            $this->assertFalse($task->canTransitionTo($target, $admin));
        }

        // Dari InProgress: Intern hanya boleh ke Submitted
        $task->status = TaskStatus::InProgress;
        $this->assertTrue($task->canTransitionTo(TaskStatus::Submitted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Assigned, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $pic));

        // Dari Submitted: Hanya PIC yang boleh ke RevisionRequested atau Accepted
        $task->status = TaskStatus::Submitted;
        $this->assertTrue($task->canTransitionTo(TaskStatus::RevisionRequested, $pic));
        $this->assertTrue($task->canTransitionTo(TaskStatus::Accepted, $pic));
        $this->assertFalse($task->canTransitionTo(TaskStatus::InProgress, $pic));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Resubmitted, $intern));

        // Dari RevisionRequested: Hanya Intern yang boleh ke Resubmitted
        $task->status = TaskStatus::RevisionRequested;
        $this->assertTrue($task->canTransitionTo(TaskStatus::Resubmitted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Submitted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $intern));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $pic));

        // Dari Resubmitted: Hanya PIC yang boleh ke RevisionRequested atau Accepted
        $task->status = TaskStatus::Resubmitted;
        $this->assertTrue($task->canTransitionTo(TaskStatus::RevisionRequested, $pic));
        $this->assertTrue($task->canTransitionTo(TaskStatus::Accepted, $pic));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Submitted, $pic));
        $this->assertFalse($task->canTransitionTo(TaskStatus::Accepted, $intern));

        // Dari Accepted (Status Final): Tidak seorang pun boleh mengubah ke status apa pun
        $task->status = TaskStatus::Accepted;
        foreach (TaskStatus::cases() as $target) {
            $this->assertFalse($task->canTransitionTo($target, $intern));
            $this->assertFalse($task->canTransitionTo($target, $pic));
            $this->assertFalse($task->canTransitionTo($target, $admin));
        }
    }

    public function test_valid_direct_and_revision_lifecycle_paths_succeed_end_to_end(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Jalur 1: assigned -> in_progress -> submitted -> accepted
        $task1 = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        $this->actingAs($intern)->patch(route('intern.tasks.start', $task1));
        $this->assertSame(TaskStatus::InProgress, $task1->fresh()->status);

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task1), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/1',
        ]);
        $this->assertSame(TaskStatus::Submitted, $task1->fresh()->status);

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task1), [
            'decision' => TaskReviewDecision::Accepted->value,
        ]);
        $this->assertSame(TaskStatus::Accepted, $task1->fresh()->status);

        // Jalur 2: assigned -> submitted (fleksibel) -> revision_requested -> resubmitted -> accepted
        $task2 = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task2), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/2',
        ]);
        $this->assertSame(TaskStatus::Submitted, $task2->fresh()->status);

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task2), [
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Perbaiki validasi form input.',
        ]);
        $this->assertSame(TaskStatus::RevisionRequested, $task2->fresh()->status);

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task2), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/2-rev',
        ]);
        $this->assertSame(TaskStatus::Resubmitted, $task2->fresh()->status);

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task2), [
            'decision' => TaskReviewDecision::Accepted->value,
        ]);
        $this->assertSame(TaskStatus::Accepted, $task2->fresh()->status);
    }

    public function test_blocks_http_tampering_and_illegal_transitions_by_intern_pic_and_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::InProgress,
        ]);

        // 1. Peserta mencoba menyisipkan status=accepted & accepted_by pada payload submission -> ditolak 422 (prohibited)
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/99',
            'status' => TaskStatus::Accepted->value,
            'accepted_by' => $intern->id,
        ])->assertSessionHasErrors(['status', 'accepted_by']);
        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);

        // 2. PIC mencoba menyisipkan status=accepted pada endpoint update tugas -> ditolak 422 (prohibited)
        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Judul Valid',
            'description' => 'Deskripsi valid minimal sepuluh karakter.',
            'deadline_at' => '2026-11-25 17:00:00',
            'status' => TaskStatus::Accepted->value,
        ])->assertSessionHasErrors('status');
        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);

        // 3. PIC mencoba langsung me-review / meng-accept tugas saat status masih in_progress -> ditolak 422
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertSessionHasErrors('status');
        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);

        // 4. Peserta submit secara sah -> status menjadi submitted
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/99',
        ])->assertSessionHasNoErrors();
        $this->assertSame(TaskStatus::Submitted, $task->fresh()->status);

        // 5. Peserta mencoba mundur dari submitted -> in_progress -> ditolak 403
        $this->actingAs($intern)->patch(route('intern.tasks.start', $task))->assertForbidden();

        // 6. Peserta mencoba submit ulang saat status masih submitted (belum direview PIC) -> ditolak 403
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/100',
        ])->assertForbidden();

        // 7. Peserta, PIC lain, dan Admin mencoba memanggil endpoint review PIC -> ditolak 403
        $this->actingAs($intern)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        $this->actingAs($otherIntern)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        $this->actingAs($otherPic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertForbidden();

        // 8. PIC menerima tugas -> status menjadi accepted (Final)
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::Accepted->value,
        ])->assertSessionHasNoErrors();
        $this->assertSame(TaskStatus::Accepted, $task->fresh()->status);

        // 9. Setelah accepted, tidak ada aksi yang bisa mengubah status atau data tugas lagi
        $this->actingAs($intern)->patch(route('intern.tasks.start', $task))->assertForbidden();
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/after-accepted',
        ])->assertForbidden();
        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Ubah Setelah Accepted',
            'description' => 'Deskripsi ubah setelah accepted.',
            'deadline_at' => '2026-11-28 17:00:00',
        ])->assertForbidden();
        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Minta revisi setelah accepted.',
        ])->assertSessionHasErrors('status');

        $this->assertSame(TaskStatus::Accepted, $task->fresh()->status);
        $this->assertSame(1, TaskSubmission::where('task_id', $task->id)->count());
    }
}
