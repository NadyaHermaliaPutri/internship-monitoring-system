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
     * @param  array{log_date: string, title: string, activity_description: string, result?: ?string}  $data
     */
    public function createManualEntry(User $intern, array $data): Logbook
    {
        return Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => $data['log_date'],
            'title' => $data['title'],
            'activity_description' => $data['activity_description'],
            'result' => $data['result'] ?? null,
            'source' => LogbookSource::Manual,
            'task_id' => null,
            'status' => LogbookStatus::Pending,
        ]);
    }

    /**
     * Memperbarui logbook manual peserta sekaligus menyimpan snapshot ke logbook_revisions.
     * Jika status sebelumnya revision_requested, status dikembalikan menjadi pending.
     *
     * @param  array{log_date: string, title: string, activity_description: string, result?: ?string}  $data
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

            $locked->update([
                'log_date' => $data['log_date'],
                'title' => $data['title'],
                'activity_description' => $data['activity_description'],
                'result' => $data['result'] ?? null,
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
}
