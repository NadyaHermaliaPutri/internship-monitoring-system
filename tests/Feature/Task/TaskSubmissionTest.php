<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 18, 15, 0, 0, 'Asia/Jakarta'));
        Storage::fake('local_private');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_intern_owner_can_submit_url_only_file_only_or_both_and_status_becomes_submitted(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Submit URL saja dari status assigned -> submitted
        $taskUrl = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        $responseUrl = $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $taskUrl), [
            'submission_url' => 'https://github.com/ptwan/internship-monitoring/pull/10',
            'submission_note' => 'Sudah diselesaikan sesuai spesifikasi.',
        ]);

        $responseUrl->assertRedirect(route('intern.tasks.show', $taskUrl));
        $this->assertSame(TaskStatus::Submitted, $taskUrl->fresh()->status);
        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $taskUrl->id,
            'submitted_by' => $intern->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/ptwan/internship-monitoring/pull/10',
            'evidence_media_id' => null,
        ]);

        // 2. Submit Video/File saja dari status in_progress -> submitted
        $taskFile = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::InProgress,
        ]);

        $videoFile = UploadedFile::fake()->create('demo_aplikasi.mp4', 12000, 'video/mp4'); // ~12 MB (valid <= 15 MB)

        $responseFile = $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $taskFile), [
            'evidence_file' => $videoFile,
            'submission_note' => 'Video demo hasil pengerjaan.',
        ]);

        $responseFile->assertRedirect(route('intern.tasks.show', $taskFile));
        $this->assertSame(TaskStatus::Submitted, $taskFile->fresh()->status);

        /** @var TaskSubmission $subFile */
        $subFile = TaskSubmission::where('task_id', $taskFile->id)->firstOrFail();
        $this->assertSame(1, $subFile->submission_number);
        $this->assertNotNull($subFile->evidence_media_id);

        // 3. Submit keduanya (URL + File PDF)
        $taskBoth = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        $pdfFile = UploadedFile::fake()->create('dokumentasi.pdf', 1500, 'application/pdf');

        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $taskBoth), [
            'submission_url' => 'https://figma.com/file/wantrace',
            'evidence_file' => $pdfFile,
        ])->assertRedirect(route('intern.tasks.show', $taskBoth));

        $this->assertSame(TaskStatus::Submitted, $taskBoth->fresh()->status);
        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $taskBoth->id,
            'submission_number' => 1,
            'submission_url' => 'https://figma.com/file/wantrace',
        ]);
    }

    public function test_resubmission_when_revision_requested_creates_new_row_and_transitions_to_resubmitted(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::Assigned,
        ]);

        // Pengumpulan ke-1
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/commit/v1',
            'submission_note' => 'Pengumpulan versi pertama.',
        ]);

        $this->assertSame(TaskStatus::Submitted, $task->fresh()->status);
        $this->assertDatabaseCount('task_submissions', 1);

        // Simulasikan PIC meminta revisi (status -> revision_requested)
        $task->update(['status' => TaskStatus::RevisionRequested]);

        // Pengumpulan ke-2 (Resubmit)
        $response2 = $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo/commit/v2',
            'submission_note' => 'Sudah diperbaiki sesuai catatan revisi PIC.',
        ]);

        $response2->assertRedirect(route('intern.tasks.show', $task));
        $this->assertSame(TaskStatus::Resubmitted, $task->fresh()->status);

        // Pastikan baris ke-1 TIDAK ditimpa dan baris ke-2 tercatat dengan submission_number = 2
        $this->assertDatabaseCount('task_submissions', 2);
        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $task->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/ptwan/repo/commit/v1',
        ]);
        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $task->id,
            'submission_number' => 2,
            'submission_url' => 'https://github.com/ptwan/repo/commit/v2',
        ]);
    }

    public function test_rejects_submission_without_evidence_or_exceeding_size_limits_or_invalid_status(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'status' => TaskStatus::InProgress,
        ]);

        // 1. Tanpa URL dan tanpa file -> ditolak
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_note' => 'Hanya mengirim catatan tanpa bukti.',
        ])->assertSessionHasErrors(['submission_url', 'evidence_file']);

        // 2. Dokumen non-video > 10 MB (mis. PDF 11 MB) -> ditolak
        $oversizedPdf = UploadedFile::fake()->create('laporan_besar.pdf', 11000, 'application/pdf');
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $oversizedPdf,
        ])->assertSessionHasErrors('evidence_file');

        // 3. Video > 15 MB (mis. MP4 16 MB) -> ditolak
        $oversizedVideo = UploadedFile::fake()->create('video_besar.mp4', 16000, 'video/mp4');
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $oversizedVideo,
        ])->assertSessionHasErrors('evidence_file');

        // 4. Ekstensi berbahaya (.exe / .php) -> ditolak
        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $invalidFile,
        ])->assertSessionHasErrors('evidence_file');

        // 5. Jika status sudah submitted atau accepted -> ditolak (403 Forbidden)
        $task->update(['status' => TaskStatus::Submitted]);
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo',
        ])->assertForbidden();

        $task->update(['status' => TaskStatus::Accepted]);
        $this->actingAs($intern)->post(route('intern.tasks.submissions.store', $task), [
            'submission_url' => 'https://github.com/ptwan/repo',
        ])->assertForbidden();
    }

    public function test_evidence_file_is_stored_privately_and_authorized_for_owner_assigned_pic_and_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picOwner = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internOwner = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $picOwner->id,
            'intern_user_id' => $internOwner->id,
            'status' => TaskStatus::InProgress,
        ]);

        $file = UploadedFile::fake()->create('hasil_kerja.pdf', 500, 'application/pdf');

        $this->actingAs($internOwner)->post(route('intern.tasks.submissions.store', $task), [
            'evidence_file' => $file,
        ])->assertRedirect(route('intern.tasks.show', $task));

        /** @var MediaFile $media */
        $media = MediaFile::firstOrFail();

        // Pastikan disimpan di local_private dengan kategori task_evidence dan nama acak (bukan nama asli)
        $this->assertSame('local_private', $media->disk);
        $this->assertSame('task_evidence', $media->category);
        $this->assertNotSame('tugas/hasil_kerja.pdf', $media->file_path);
        Storage::disk('local_private')->assertExists($media->file_path);
        Storage::disk('public')->assertMissing($media->file_path);

        // Pemilik tugas (internOwner), PIC penanggung jawab tugas (picOwner), dan Admin diizinkan (200)
        $this->actingAs($internOwner)
            ->get(route('media.private.show', $media))
            ->assertOk();

        $this->actingAs($picOwner)
            ->get(route('media.private.show', $media))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('media.private.show', $media))
            ->assertOk();

        // Peserta lain dan PIC lain wajib ditolak (403 Forbidden)
        $this->actingAs($otherIntern)
            ->get(route('media.private.show', $media))
            ->assertForbidden();

        $this->actingAs($otherPic)
            ->get(route('media.private.show', $media))
            ->assertForbidden();
    }

    public function test_other_intern_cannot_submit_to_another_interns_task(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $taskA = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $internA->id,
            'status' => TaskStatus::Assigned,
        ]);

        $this->actingAs($internB)
            ->post(route('intern.tasks.submissions.store', $taskA), [
                'submission_url' => 'https://github.com/ptwan/hijack',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('task_submissions', 0);
        $this->assertSame(TaskStatus::Assigned, $taskA->fresh()->status);
    }
}
