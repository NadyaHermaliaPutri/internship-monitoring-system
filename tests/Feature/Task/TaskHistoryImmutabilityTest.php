<?php

namespace Tests\Feature\Task;

use App\Enums\TaskReviewDecision;
use App\Enums\TaskStatus;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class TaskHistoryImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 10, 0, 0, 'Asia/Jakarta'));
        Storage::fake('local_private');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_three_rounds_of_submissions_and_reviews_preserve_all_database_rows_files_and_timeline_views(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Integrasi Modul Monitoring Magang',
            'status' => TaskStatus::InProgress,
        ]);

        // =====================================================================
        // PUTARAN 1: Submit v1 (File A + catatan v1) -> Review v1 (revision_requested)
        // =====================================================================
        $fileA = UploadedFile::fake()->create('dokumen_v1.pdf', 800, 'application/pdf');

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $fileA,
            'submission_note' => 'Catatan pengumpulan versi pertama (v1).',
        ])->assertRedirect(route('intern.tasks.show', $task));

        /** @var TaskSubmission $sub1 */
        $sub1 = TaskSubmission::where('task_id', $task->id)->where('submission_number', 1)->firstOrFail();
        /** @var MediaFile $mediaA */
        $mediaA = MediaFile::findOrFail($sub1->evidence_media_id);

        Carbon::setTestNow(Carbon::create(2026, 11, 20, 11, 0, 0, 'Asia/Jakarta'));

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $sub1->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Feedback PIC v1: Mohon lengkapi diagram alur dan sertakan URL.',
        ])->assertRedirect(route('pic.tasks.show', $task));

        // =====================================================================
        // PUTARAN 2: Resubmit v2 (File B + URL v2 + catatan v2) -> Review v2 (revision_requested)
        // =====================================================================
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 13, 0, 0, 'Asia/Jakarta'));
        $fileB = UploadedFile::fake()->create('video_demo_v2.mp4', 4500, 'video/mp4');

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $fileB,
            'submission_url' => 'https://github.com/ptwan/repo/pull/20-v2',
            'submission_note' => 'Catatan pengumpulan versi kedua (v2) beserta video.',
        ])->assertRedirect(route('intern.tasks.show', $task));

        /** @var TaskSubmission $sub2 */
        $sub2 = TaskSubmission::where('task_id', $task->id)->where('submission_number', 2)->firstOrFail();
        /** @var MediaFile $mediaB */
        $mediaB = MediaFile::findOrFail($sub2->evidence_media_id);

        Carbon::setTestNow(Carbon::create(2026, 11, 20, 14, 0, 0, 'Asia/Jakarta'));

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $sub2->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Feedback PIC v2: Video sudah bagus, rapikan sedikit penamaan variabel.',
        ])->assertRedirect(route('pic.tasks.show', $task));

        // =====================================================================
        // PUTARAN 3: Resubmit v3 (URL v3 + catatan v3) -> Review v3 (accepted)
        // =====================================================================
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 15, 30, 0, 'Asia/Jakarta'));

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/pull/20-v3-final',
            'submission_note' => 'Catatan pengumpulan versi ketiga (v3 final).',
        ])->assertRedirect(route('intern.tasks.show', $task));

        /** @var TaskSubmission $sub3 */
        $sub3 = TaskSubmission::where('task_id', $task->id)->where('submission_number', 3)->firstOrFail();

        Carbon::setTestNow(Carbon::create(2026, 11, 20, 16, 0, 0, 'Asia/Jakarta'));

        $this->actingAs($pic)->post(route('pic.tasks.reviews.store', $task), [
            'task_submission_id' => $sub3->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Feedback PIC v3: Seluruh revisi telah tuntas dan diterima.',
        ])->assertRedirect(route('pic.tasks.show', $task));

        // =====================================================================
        // VERIFIKASI 1: Database memiliki tepat 3 submission & 3 review yang utuh
        // =====================================================================
        $this->assertDatabaseCount('task_submissions', 3);
        $this->assertDatabaseCount('task_reviews', 3);

        $sub1Fresh = $sub1->fresh();
        $sub2Fresh = $sub2->fresh();
        $sub3Fresh = $sub3->fresh();

        $this->assertSame(1, $sub1Fresh->submission_number);
        $this->assertSame('Catatan pengumpulan versi pertama (v1).', $sub1Fresh->submission_note);
        $this->assertNull($sub1Fresh->submission_url);
        $this->assertSame($mediaA->id, $sub1Fresh->evidence_media_id);

        $this->assertSame(2, $sub2Fresh->submission_number);
        $this->assertSame('Catatan pengumpulan versi kedua (v2) beserta video.', $sub2Fresh->submission_note);
        $this->assertSame('https://github.com/ptwan/repo/pull/20-v2', $sub2Fresh->submission_url);
        $this->assertSame($mediaB->id, $sub2Fresh->evidence_media_id);

        $this->assertSame(3, $sub3Fresh->submission_number);
        $this->assertSame('Catatan pengumpulan versi ketiga (v3 final).', $sub3Fresh->submission_note);
        $this->assertSame('https://github.com/ptwan/repo/pull/20-v3-final', $sub3Fresh->submission_url);
        $this->assertNull($sub3Fresh->evidence_media_id);

        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $sub1->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Feedback PIC v1: Mohon lengkapi diagram alur dan sertakan URL.',
        ]);
        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $sub2->id,
            'decision' => TaskReviewDecision::RevisionRequested->value,
            'feedback' => 'Feedback PIC v2: Video sudah bagus, rapikan sedikit penamaan variabel.',
        ]);
        $this->assertDatabaseHas('task_reviews', [
            'task_submission_id' => $sub3->id,
            'decision' => TaskReviewDecision::Accepted->value,
            'feedback' => 'Feedback PIC v3: Seluruh revisi telah tuntas dan diterima.',
        ]);

        // =====================================================================
        // VERIFIKASI 2: File A (v1) dan File B (v2) tetap ada di local_private & 200 OK
        // =====================================================================
        Storage::disk('local_private')->assertExists($mediaA->file_path);
        Storage::disk('local_private')->assertExists($mediaB->file_path);

        $this->actingAs($intern)->get(route('media.private.show', $mediaA))->assertOk();
        $this->actingAs($intern)->get(route('media.private.show', $mediaB))->assertOk();
        $this->actingAs($pic)->get(route('media.private.show', $mediaA))->assertOk();
        $this->actingAs($pic)->get(route('media.private.show', $mediaB))->assertOk();

        // File bukti tugas (task_evidence) juga tidak boleh dihapus menurut MediaFilePolicy::delete
        $this->assertFalse(Gate::forUser($intern)->allows('delete', $mediaA));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $mediaA));

        // =====================================================================
        // VERIFIKASI 3: Halaman detail tugas (Peserta, PIC, Admin) menampilkan v1, v2, v3 lengkap
        // =====================================================================
        foreach ([[$intern, 'intern.tasks.show'], [$pic, 'pic.tasks.show'], [$admin, 'admin.tasks.show']] as [$viewer, $routeName]) {
            $page = $this->actingAs($viewer)->get(route($routeName, $task));
            $page->assertOk();
            $page->assertSee('Pengumpulan Ke-1');
            $page->assertSee('Pengumpulan Ke-2');
            $page->assertSee('Pengumpulan Ke-3');
            $page->assertSee('Catatan pengumpulan versi pertama (v1).');
            $page->assertSee('Catatan pengumpulan versi kedua (v2) beserta video.');
            $page->assertSee('Catatan pengumpulan versi ketiga (v3 final).');
            $page->assertSee('Feedback PIC v1: Mohon lengkapi diagram alur dan sertakan URL.');
            $page->assertSee('Feedback PIC v2: Video sudah bagus, rapikan sedikit penamaan variabel.');
            $page->assertSee('Feedback PIC v3: Seluruh revisi telah tuntas dan diterima.');
            $page->assertSee('dokumen_v1.pdf');
            $page->assertSee('video_demo_v2.mp4');
        }
    }

    public function test_models_and_routes_strictly_forbid_updating_or_deleting_task_submissions_and_reviews(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Submitted,
        ]);

        $submission = TaskSubmission::create([
            'task_id' => $task->id,
            'submitted_by' => $intern->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/ptwan/repo/v1',
            'submitted_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $review = TaskReview::create([
            'task_submission_id' => $submission->id,
            'reviewed_by' => $pic->id,
            'decision' => TaskReviewDecision::RevisionRequested,
            'feedback' => 'Catatan awal.',
            'reviewed_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // 1. Pastikan tidak ada named route untuk edit/update/destroy submission maupun review
        $this->assertFalse(Route::has('intern.tasks.submissions.update'));
        $this->assertFalse(Route::has('intern.tasks.submissions.destroy'));
        $this->assertFalse(Route::has('pic.tasks.reviews.update'));
        $this->assertFalse(Route::has('pic.tasks.reviews.destroy'));

        // 2. Pastikan model guard menolak update() pada TaskSubmission
        $submissionUpdateBlocked = false;
        try {
            $submission->update(['submission_note' => 'Coba timpa histori']);
        } catch (LogicException) {
            $submissionUpdateBlocked = true;
        }
        $this->assertTrue($submissionUpdateBlocked, 'TaskSubmission::update() harus memicu LogicException.');

        // 3. Pastikan model guard menolak delete() pada TaskSubmission
        $submissionDeleteBlocked = false;
        try {
            $submission->delete();
        } catch (LogicException) {
            $submissionDeleteBlocked = true;
        }
        $this->assertTrue($submissionDeleteBlocked, 'TaskSubmission::delete() harus memicu LogicException.');

        // 4. Pastikan model guard menolak update() dan delete() pada TaskReview
        $reviewUpdateBlocked = false;
        try {
            $review->update(['feedback' => 'Coba timpa feedback']);
        } catch (LogicException) {
            $reviewUpdateBlocked = true;
        }
        $this->assertTrue($reviewUpdateBlocked, 'TaskReview::update() harus memicu LogicException.');

        $reviewDeleteBlocked = false;
        try {
            $review->delete();
        } catch (LogicException) {
            $reviewDeleteBlocked = true;
        }
        $this->assertTrue($reviewDeleteBlocked, 'TaskReview::delete() harus memicu LogicException.');
    }
}
