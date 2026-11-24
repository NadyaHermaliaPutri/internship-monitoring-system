<?php

namespace App\Services;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\LogbookRevision;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LogbookService
{
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
     * Menyimpan entri logbook manual baru milik peserta.
     *
     * @param  array{log_date: string, title: string, activity_description: string, result?: ?string, related_task_id?: ?int}  $data
     */
    public function createManualEntry(User $intern, array $data): Logbook
    {
        $relatedTaskId = ! empty($data['related_task_id']) ? (int) $data['related_task_id'] : null;

        return Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => $data['log_date'],
            'title' => $data['title'],
            'activity_description' => $data['activity_description'],
            'result' => $data['result'] ?? null,
            'source' => LogbookSource::Manual,
            'task_id' => null,
            'related_task_id' => $relatedTaskId,
            'status' => LogbookStatus::Pending,
        ]);
    }

    /**
     * Memperbarui logbook manual peserta sekaligus menyimpan snapshot ke logbook_revisions.
     * Jika status sebelumnya revision_requested, status dikembalikan menjadi pending.
     *
     * @param  array{log_date: string, title: string, activity_description: string, result?: ?string, related_task_id?: ?int}  $data
     */
    public function updateManualEntry(Logbook $logbook, User $actor, array $data): Logbook
    {
        return DB::transaction(function () use ($logbook, $actor, $data) {
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
                'status_before' => $locked->status?->value,
                'review_note_before' => $locked->review_note,
                'revised_by' => $actor->id,
            ]);

            $relatedTaskId = array_key_exists('related_task_id', $data)
                ? (! empty($data['related_task_id']) ? (int) $data['related_task_id'] : null)
                : $locked->related_task_id;

            $locked->update([
                'log_date' => $data['log_date'],
                'title' => $data['title'],
                'activity_description' => $data['activity_description'],
                'result' => $data['result'] ?? null,
                'related_task_id' => $relatedTaskId,
                'status' => LogbookStatus::Pending,
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

            if ($locked->status !== LogbookStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya logbook berstatus Menunggu Review (Pending) yang dapat ditinjau oleh PIC.',
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

            $locked->update([
                'status' => $decision,
                'reviewed_by' => $pic->id,
                'reviewed_at' => Carbon::now(Logbook::TIMEZONE),
                'review_note' => $note,
            ]);

            return $locked->refresh();
        });
    }
}
