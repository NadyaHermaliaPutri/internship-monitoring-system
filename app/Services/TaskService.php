<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
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

            if (! $intern->isIntern() || ! $intern->is_active || $locked->intern_user_id !== $intern->id) {
                throw ValidationException::withMessages([
                    'task' => 'Anda tidak berwenang memulai tugas ini.',
                ]);
            }

            if (! $locked->canBeStartedByIntern()) {
                throw ValidationException::withMessages([
                    'status' => 'Tugas hanya dapat dimulai ketika berstatus Ditugaskan (Assigned).',
                ]);
            }

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

            if (! $intern->isIntern() || ! $intern->is_active || $locked->intern_user_id !== $intern->id) {
                throw ValidationException::withMessages([
                    'task' => 'Anda tidak berwenang mengumpulkan hasil untuk tugas ini.',
                ]);
            }

            if (! $locked->canBeSubmittedByIntern()) {
                throw ValidationException::withMessages([
                    'status' => 'Tugas dengan status saat ini tidak dapat dikumpulkan.',
                ]);
            }

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
            $nextStatus = $locked->status->nextStatusAfterSubmission();

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
}
