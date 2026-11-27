<?php

namespace App\Services;

use App\Enums\LogbookActivityStatus;
use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\LogbookRevision;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LogbookService
{
    public function __construct(
        private readonly MediaStorageService $mediaStorageService
    ) {}

    /**
     * Mengambil tanggal hari ini sesuai standar waktu server Asia/Jakarta.
     */
    public function todayDateString(): string
    {
        return Carbon::now(Logbook::TIMEZONE)->toDateString();
    }

    /**
     * Mengambil batas tanggal terawal yang diizinkan untuk input mundur (H - MAX_BACKDATE_DAYS).
     */
    public function earliestAllowedDateString(): string
    {
        return Carbon::now(Logbook::TIMEZONE)
            ->subDays(Logbook::MAX_BACKDATE_DAYS)
            ->toDateString();
    }

    /**
     * Titik sambungan (hook) validasi hari kerja/libur untuk diintegrasikan dengan service Kalender (Modul 2).
     * Saat service kalender Modul 2 tersedia, pengecekan dapat dihubungkan di metode ini tanpa mengubah controller/request.
     */
    public function isAllowedLogbookDate(string $date): bool
    {
        $parsed = Carbon::parse($date, Logbook::TIMEZONE)->startOfDay();
        $today = Carbon::now(Logbook::TIMEZONE)->startOfDay();
        $earliest = $today->copy()->subDays(Logbook::MAX_BACKDATE_DAYS);

        if ($parsed->gt($today) || $parsed->lt($earliest)) {
            return false;
        }

        // Hook integrasi Modul 2 (Workday / Calendar Service):
        // Contoh nantinya: if (app()->bound(WorkdayServiceContract::class)) { ... }
        return true;
    }

    /**
     * Kolom activity_description tetap NOT NULL di database; jika form tidak mengirim uraian
     * (form ringkas hanya berisi "Kegiatan"), isinya disamakan dengan title.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveActivityDescription(array $data): string
    {
        $description = isset($data['activity_description']) ? trim((string) $data['activity_description']) : '';

        return $description !== '' ? $description : (string) $data['title'];
    }

    /**
     * Menyimpan entri logbook manual baru milik peserta.
     *
     * @param  array{log_date: string, title: string, activity_description?: ?string, result?: ?string, related_task_id?: ?int}  $data
     */
    public function createManualEntry(User $intern, array $data, ?UploadedFile $evidenceFile = null): Logbook
    {
        return DB::transaction(function () use ($intern, $data, $evidenceFile) {
            $relatedTaskId = ! empty($data['related_task_id']) ? (int) $data['related_task_id'] : null;
            $targetPicUserId = ! empty($data['target_pic_user_id']) ? (int) $data['target_pic_user_id'] : null;
            $result = isset($data['result']) && trim((string) $data['result']) !== ''
                ? trim((string) $data['result'])
                : null;

            $evidenceMediaId = null;
            if ($evidenceFile !== null) {
                $media = $this->mediaStorageService->storeUploadedFile($evidenceFile, $intern, 'logbook_evidence');
                $evidenceMediaId = $media->id;
            }

            return Logbook::create([
                'intern_user_id' => $intern->id,
                'log_date' => $data['log_date'],
                'title' => $data['title'],
                'activity_description' => $this->resolveActivityDescription($data),
                'result' => $result,
                'evidence_media_id' => $evidenceMediaId,
                'activity_status' => LogbookActivityStatus::SedangDikerjakan->value,
                'source' => LogbookSource::Manual,
                'task_id' => null,
                'related_task_id' => $relatedTaskId,
                'target_pic_user_id' => $targetPicUserId,
                'status' => null,
            ]);
        });
    }

    /**
     * Memperbarui logbook manual peserta sekaligus menyimpan snapshot ke logbook_revisions.
     * Jika status sebelumnya revision_requested, status dikembalikan menjadi pending.
     * File bukti lama TIDAK pernah dihapus (append-only history).
     *
     * @param  array{log_date: string, title: string, activity_description?: ?string, result?: ?string, related_task_id?: ?int}  $data
     */
    public function updateManualEntry(Logbook $logbook, User $actor, array $data, ?UploadedFile $evidenceFile = null): Logbook
    {
        return DB::transaction(function () use ($logbook, $actor, $data, $evidenceFile) {
            /** @var Logbook $locked */
            $locked = Logbook::query()->whereKey($logbook->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canBeEditedByIntern()) {
                throw ValidationException::withMessages([
                    'logbook' => 'Logbook yang sudah disetujui atau berasal dari penugasan otomatis tidak dapat diubah.',
                ]);
            }

            $nextRevisionNo = ((int) $locked->revisions()->max('revision_no')) + 1;

            LogbookRevision::create([
                'logbook_id' => $locked->id,
                'revision_no' => $nextRevisionNo,
                'log_date' => $locked->log_date->toDateString(),
                'title' => $locked->title,
                'activity_description' => $locked->activity_description,
                'result' => $locked->result,
                'evidence_media_id' => $locked->evidence_media_id,
                'target_pic_user_id' => $locked->target_pic_user_id,
                'activity_status' => $locked->activity_status?->value,
                'status_before' => $locked->status?->value,
                'review_note_before' => $locked->review_note,
                'revised_by' => $actor->id,
            ]);

            $relatedTaskId = array_key_exists('related_task_id', $data)
                ? (! empty($data['related_task_id']) ? (int) $data['related_task_id'] : null)
                : $locked->related_task_id;

            $targetPicUserId = array_key_exists('target_pic_user_id', $data)
                ? (! empty($data['target_pic_user_id']) ? (int) $data['target_pic_user_id'] : null)
                : $locked->target_pic_user_id;

            $result = isset($data['result']) && trim((string) $data['result']) !== ''
                ? trim((string) $data['result'])
                : null;

            $evidenceMediaId = $locked->evidence_media_id;
            if ($evidenceFile !== null) {
                $media = $this->mediaStorageService->storeUploadedFile($evidenceFile, $actor, 'logbook_evidence');
                $evidenceMediaId = $media->id;
            }

            if ($locked->status === LogbookStatus::RevisionRequested) {
                $resolvedActivityStatus = LogbookActivityStatus::Selesai;
            } elseif (array_key_exists('activity_status', $data) && ! empty($data['activity_status'])) {
                $resolvedActivityStatus = LogbookActivityStatus::from((string) $data['activity_status']);
            } else {
                $resolvedActivityStatus = $locked->activity_status ?? LogbookActivityStatus::SedangDikerjakan;
            }

            $nextStatus = null;
            if ($targetPicUserId !== null && $resolvedActivityStatus === LogbookActivityStatus::Selesai) {
                $nextStatus = LogbookStatus::Pending;
            }

            $locked->update([
                'log_date' => $data['log_date'],
                'title' => $data['title'],
                'activity_description' => $this->resolveActivityDescription($data),
                'result' => $result,
                'evidence_media_id' => $evidenceMediaId,
                'activity_status' => $resolvedActivityStatus->value,
                'related_task_id' => $relatedTaskId,
                'target_pic_user_id' => $targetPicUserId,
                'status' => $nextStatus,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Mencatat logbook otomatis saat PIC memberikan tugas kepada peserta.
     * Menggunakan firstOrCreate pada task_id (unique) agar aman dari duplikasi saat update/refresh tugas.
     * Status dibiarkan NULL agar tidak masuk antrian approval dan tidak dihitung sebagai pemenuhan EWS.
     */
    public function recordTaskAssignmentEntry(
        int $internUserId,
        int $taskId,
        string $taskTitle,
        string $activityDescription,
        ?string $logDate = null
    ): Logbook {
        $date = $logDate ?? $this->todayDateString();

        return Logbook::firstOrCreate(
            ['task_id' => $taskId],
            [
                'intern_user_id' => $internUserId,
                'log_date' => $date,
                'title' => $taskTitle,
                'activity_description' => $activityDescription,
                'result' => null,
                'activity_status' => LogbookActivityStatus::BaruDikasih->value,
                'source' => LogbookSource::TaskAssignment,
                'status' => null,
            ]
        );
    }

    /**
     * Meninjau logbook manual peserta oleh PIC yang berwenang (dalam DB::transaction + lockForUpdate):
     * - Hanya logbook manual (source = manual) berstatus pending yang dapat direview
     * - Keputusan approved: mengunci status menjadi approved
     * - Keputusan revision_requested: wajib menyertakan review_note (min 5 karakter)
     *
     * @param  array{decision: string, review_note?: ?string}  $data
     */
    public function reviewLogbook(Logbook $logbook, User $pic, array $data): Logbook
    {
        return DB::transaction(function () use ($logbook, $pic, $data) {
            /** @var Logbook $locked */
            $locked = Logbook::query()->whereKey($logbook->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isManual()) {
                throw ValidationException::withMessages([
                    'source' => 'Logbook otomatis dari penugasan PIC tidak memerlukan dan tidak dapat melalui proses approval.',
                ]);
            }

            if ($locked->activity_status !== LogbookActivityStatus::Selesai || $locked->status !== LogbookStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya logbook yang sudah ditandai Selesai dan berstatus Menunggu Review (Pending) yang dapat ditinjau oleh PIC.',
                ]);
            }

            if (! $pic->isPic() || ! $pic->is_active || ! $locked->isReviewableByPic($pic->id)) {
                throw ValidationException::withMessages([
                    'logbook' => 'Anda tidak berwenang meninjau logbook ini.',
                ]);
            }

            $decision = LogbookStatus::from($data['decision']);
            if (! in_array($decision, [LogbookStatus::Approved, LogbookStatus::RevisionRequested], true)) {
                throw ValidationException::withMessages([
                    'decision' => 'Keputusan review hanya boleh Disetujui (Approved) atau Perlu Revisi (Revision Requested).',
                ]);
            }

            $note = isset($data['review_note']) && trim((string) $data['review_note']) !== ''
                ? trim((string) $data['review_note'])
                : null;

            if ($decision === LogbookStatus::RevisionRequested && ($note === null || mb_strlen($note) < 5)) {
                throw ValidationException::withMessages([
                    'review_note' => 'Catatan revisi wajib diisi minimal 5 karakter ketika meminta revisi logbook.',
                ]);
            }

            $updatePayload = [
                'status' => $decision,
                'reviewed_by' => $pic->id,
                'reviewed_at' => Carbon::now(Logbook::TIMEZONE),
                'review_note' => $note,
            ];

            if ($decision === LogbookStatus::Approved) {
                $updatePayload['activity_status'] = LogbookActivityStatus::Selesai->value;
            }

            $locked->update($updatePayload);

            return $locked->refresh();
        });
    }

    /**
     * Menandai progres kegiatan manual peserta menjadi "Selesai":
     * - Jika ditujukan ke PIC (target_pic_user_id terisi) -> status otomatis menjadi Pending (Menunggu Review PIC).
     * - Jika Mandiri (target_pic_user_id = null) -> status tetap null (hanya Selesai untuk pantauan Admin).
     */
    public function completeManualEntry(Logbook $logbook, User $actor): Logbook
    {
        return DB::transaction(function () use ($logbook, $actor) {
            /** @var Logbook $locked */
            $locked = Logbook::query()->whereKey($logbook->id)->lockForUpdate()->firstOrFail();

            if (! $actor->isIntern() || $locked->intern_user_id !== $actor->id || ! $locked->canBeCompletedByIntern()) {
                throw ValidationException::withMessages([
                    'logbook' => 'Hanya logbook kegiatan milik Anda yang masih Sedang Dikerjakan dan belum disetujui yang dapat ditandai Selesai.',
                ]);
            }

            $locked->update([
                'activity_status' => LogbookActivityStatus::Selesai->value,
                'status' => $locked->target_pic_user_id !== null ? LogbookStatus::Pending : null,
            ]);

            return $locked->refresh();
        });
    }
}
