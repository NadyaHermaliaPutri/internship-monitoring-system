<?php

namespace App\Services;

use App\Enums\TaskReviewDecision;
use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(
        private readonly LogbookService $logbookService,
        private readonly MediaStorageService $mediaStorageService
    ) {}

    /**
     * Mengambil waktu server saat ini sesuai standar Asia/Jakarta.
     */
    public function now(): Carbon
    {
        return Carbon::now(Task::TIMEZONE);
    }

    /**
     * Memeriksa apakah peserta adalah user role=intern, is_active=true,
     * dan memiliki penugasan aktif kepada PIC terkait di pic_assignments pada tanggal hari ini (Asia/Jakarta).
     */
    public function isInternAssignableByPic(int $picUserId, int $internUserId, ?string $onDate = null): bool
    {
        $intern = User::query()
            ->whereKey($internUserId)
            ->where('role', 'intern')
            ->where('is_active', true)
            ->first();

        if (! $intern) {
            return false;
        }

        $date = $onDate ?? $this->now()->toDateString();

        return Logbook::isPicAuthorizedForInternOnDate($picUserId, $internUserId, $date);
    }

    /**
     * Mengambil daftar peserta aktif yang sedang berada dalam penugasan aktif PIC pada hari ini (Asia/Jakarta).
     *
     * @return Collection<int, User>
     */
    public function getActiveAssignedInternsForPic(int $picUserId): Collection
    {
        $today = $this->now()->toDateString();

        return User::query()
            ->where('role', 'intern')
            ->where('is_active', true)
            ->whereExists(function ($sub) use ($picUserId, $today) {
                $sub->select(DB::raw(1))
                    ->from('pic_assignments')
                    ->whereColumn('pic_assignments.intern_user_id', 'users.id')
                    ->where('pic_assignments.pic_user_id', $picUserId)
                    ->whereDate('pic_assignments.start_date', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('pic_assignments.end_date')
                            ->orWhereDate('pic_assignments.end_date', '>=', $today);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Membuat tugas baru dengan status 'assigned' sekaligus mencatat 1 record otomatis
     * di tabel logbooks dalam 1 DB Transaction yang sama.
     *
     * @param  array{intern_user_id: int, title: string, description: string, deadline_at: string}  $data
     */
    public function createTask(User $pic, array $data): Task
    {
        $assignedAt = $this->now();
        $deadlineAt = Carbon::parse($data['deadline_at'], Task::TIMEZONE);

        if (! $this->isInternAssignableByPic($pic->id, (int) $data['intern_user_id'], $assignedAt->toDateString())) {
            throw ValidationException::withMessages([
                'intern_user_id' => 'Peserta tidak valid, tidak aktif, atau tidak berada dalam penugasan aktif Anda.',
            ]);
        }

        return DB::transaction(function () use ($pic, $data, $assignedAt, $deadlineAt) {
            $task = Task::create([
                'pic_user_id' => $pic->id,
                'intern_user_id' => (int) $data['intern_user_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'assigned_at' => $assignedAt,
                'deadline_at' => $deadlineAt,
                'status' => TaskStatus::Assigned,
            ]);

            $logbookTitle = Str::limit('Penugasan PIC: '.$task->title, 150, '');
            $logbookDescription = sprintf(
                "PIC (%s) memberikan tugas baru: \"%s\" dengan batas waktu pengumpulan %s WIB.\n\nDeskripsi Tugas:\n%s",
                $pic->name,
                $task->title,
                $deadlineAt->translatedFormat('d M Y H:i'),
                $task->description
            );

            $this->logbookService->recordTaskAssignmentEntry(
                internUserId: $task->intern_user_id,
                taskId: $task->id,
                taskTitle: $logbookTitle,
                activityDescription: $logbookDescription,
                logDate: $assignedAt->toDateString()
            );

            return $task;
        });
    }

    /**
     * Memperbarui detail tugas (judul, deskripsi, atau perpanjangan deadline_at) oleh PIC.
     * Tidak membuat baris logbook otomatis baru (anti-duplikasi).
     *
     * @param  array{title: string, description: string, deadline_at: string}  $data
     */
    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            /** @var Task $locked */
            $locked = Task::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canBeEditedByPic()) {
                throw ValidationException::withMessages([
                    'task' => 'Tugas yang sudah diterima (accepted) tidak dapat diubah.',
                ]);
            }

            $deadlineAt = Carbon::parse($data['deadline_at'], Task::TIMEZONE);

            $locked->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'deadline_at' => $deadlineAt,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Transisi status tugas oleh peserta pemilik dari assigned -> in_progress.
     * Menolak keras transisi dari status selain assigned atau percobaan mengubah ke accepted.
     */
    public function startTask(Task $task, User $intern): Task
    {
        return DB::transaction(function () use ($task, $intern) {
            /** @var Task $locked */
            $locked = Task::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();

            $locked->assertCanTransitionTo(TaskStatus::InProgress, $intern);

            $locked->update([
                'status' => TaskStatus::InProgress,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Menyimpan pengumpulan hasil tugas (baris baru di task_submissions, tidak pernah menimpa baris lama),
     * menyimpan file bukti ke private storage (jika ada), dan memperbarui status tugas:
     * - assigned / in_progress -> submitted
     * - revision_requested -> resubmitted
     *
     * @param  array{submission_url?: ?string, submission_note?: ?string}  $data
     */
    public function submitTask(Task $task, User $intern, array $data, ?UploadedFile $evidenceFile = null): TaskSubmission
    {
        return DB::transaction(function () use ($task, $intern, $data, $evidenceFile) {
            /** @var Task $locked */
            $locked = Task::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();

            $nextStatus = $locked->status?->nextStatusAfterSubmission() ?? TaskStatus::Submitted;
            $locked->assertCanTransitionTo($nextStatus, $intern);

            $url = isset($data['submission_url']) && trim((string) $data['submission_url']) !== ''
                ? trim((string) $data['submission_url'])
                : null;

            if ($url === null && $evidenceFile === null) {
                throw ValidationException::withMessages([
                    'submission_url' => 'Minimal salah satu bukti tugas (tautan URL atau berkas/video) wajib diisi.',
                ]);
            }

            $mediaFileId = null;
            if ($evidenceFile !== null) {
                $mediaFile = $this->mediaStorageService->storeUploadedFile(
                    file: $evidenceFile,
                    owner: $intern,
                    category: 'task_evidence'
                );
                $mediaFileId = $mediaFile->id;
            }

            $nextSubmissionNumber = ((int) $locked->submissions()->max('submission_number')) + 1;

            $submission = TaskSubmission::create([
                'task_id' => $locked->id,
                'submitted_by' => $intern->id,
                'submission_number' => $nextSubmissionNumber,
                'submission_note' => $data['submission_note'] ?? null,
                'submission_url' => $url,
                'evidence_media_id' => $mediaFileId,
                'submitted_at' => $this->now(),
            ]);

            $locked->update([
                'status' => $nextStatus,
            ]);

            return $submission;
        });
    }

    /**
     * Meninjau pengumpulan hasil tugas terbaru oleh PIC penanggung jawab tugas.
     * Selalu membuat baris baru di task_reviews (tidak pernah menimpa review lama) di dalam DB::transaction:
     * - revision_requested: wajib feedback, mengubah status tugas -> revision_requested
     * - accepted: mengubah status tugas -> accepted, mengisi accepted_at & accepted_by
     *
     * @param  array{decision: string, feedback?: ?string, task_submission_id?: ?int}  $data
     */
    public function reviewTask(Task $task, User $pic, array $data): TaskReview
    {
        return DB::transaction(function () use ($task, $pic, $data) {
            /** @var Task $locked */
            $locked = Task::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();

            $decision = TaskReviewDecision::from($data['decision']);
            $targetStatus = $decision->toTaskStatus();

            $locked->assertCanTransitionTo($targetStatus, $pic);

            /** @var TaskSubmission|null $latestSubmission */
            $latestSubmission = $locked->submissions()->orderByDesc('submission_number')->first();

            if (! $latestSubmission) {
                throw ValidationException::withMessages([
                    'task_submission_id' => 'Belum ada pengumpulan hasil tugas yang dapat ditinjau.',
                ]);
            }

            if (! empty($data['task_submission_id']) && (int) $data['task_submission_id'] !== $latestSubmission->id) {
                throw ValidationException::withMessages([
                    'task_submission_id' => 'Peninjauan hanya dapat dilakukan pada pengumpulan (submission) versi terbaru.',
                ]);
            }

            // Cegah duplikasi review pada submission terbaru yang sudah pernah direview
            if ($latestSubmission->reviews()->exists()) {
                throw ValidationException::withMessages([
                    'task_submission_id' => 'Pengumpulan versi terbaru ini sudah pernah ditinjau.',
                ]);
            }

            $feedback = isset($data['feedback']) && trim((string) $data['feedback']) !== ''
                ? trim((string) $data['feedback'])
                : null;

            if ($decision === TaskReviewDecision::RevisionRequested && ($feedback === null || mb_strlen($feedback) < 5)) {
                throw ValidationException::withMessages([
                    'feedback' => 'Catatan revisi (feedback) wajib diisi minimal 5 karakter ketika meminta revisi.',
                ]);
            }

            $now = $this->now();

            $review = TaskReview::create([
                'task_submission_id' => $latestSubmission->id,
                'reviewed_by' => $pic->id,
                'decision' => $decision,
                'feedback' => $feedback,
                'reviewed_at' => $now,
            ]);

            if ($decision === TaskReviewDecision::Accepted) {
                $locked->update([
                    'status' => TaskStatus::Accepted,
                    'accepted_at' => $now,
                    'accepted_by' => $pic->id,
                ]);
            } else {
                $locked->update([
                    'status' => TaskStatus::RevisionRequested,
                ]);
            }

            return $review;
        });
    }
}
