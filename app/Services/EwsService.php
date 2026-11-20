<?php

namespace App\Services;

use App\Enums\LogbookSource;
use App\Models\Attendance;
use App\Models\EwsSetting;
use App\Models\Logbook;
use App\Models\PicAssignment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EwsService
{
    public function __construct(
        protected WorkCalendarService $calendarService,
        protected AttendanceRecapService $recapService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Jam batas cutoff EWS presensi masuk (WIB). Default: 09:00.
     * Catatan: 08:05 adalah batas keterlambatan check-in (on_time vs late),
     * sedangkan 09:00 (editable oleh Admin) adalah batas alarm EWS "Belum Presensi".
     */
    public function getCutoffPresensiTime(): string
    {
        try {
            return EwsSetting::getSettings()->attendance_cutoff_time ?? '09:00';
        } catch (\Throwable) {
            return '09:00';
        }
    }

    /**
     * Jam batas cutoff EWS pengisian logbook harian (WIB). Default: 17:00.
     */
    public function getCutoffLogbookTime(): string
    {
        try {
            return EwsSetting::getSettings()->logbook_cutoff_time ?? '17:00';
        } catch (\Throwable) {
            return '17:00';
        }
    }

    /**
     * Memperbarui pengaturan cutoff EWS dan mencatat riwayat ke tabel audit_logs.
     */
    public function updateCutoffSettings(string $attendanceCutoff, string $logbookCutoff, User $admin): EwsSetting
    {
        $setting = EwsSetting::getSettings();

        $oldValues = [
            'attendance_cutoff_time' => $setting->attendance_cutoff_time,
            'logbook_cutoff_time' => $setting->logbook_cutoff_time,
        ];

        $setting->attendance_cutoff_time = $attendanceCutoff;
        $setting->logbook_cutoff_time = $logbookCutoff;
        $setting->updated_by = $admin->id;
        $setting->save();

        $newValues = [
            'attendance_cutoff_time' => $setting->attendance_cutoff_time,
            'logbook_cutoff_time' => $setting->logbook_cutoff_time,
        ];

        $this->auditLogService->log(
            action: 'ews_settings_updated',
            entityType: 'ews_settings',
            entityId: $setting->id,
            oldValues: $oldValues,
            newValues: $newValues,
            actor: $admin
        );

        return $setting;
    }

    /**
     * Indikator 1: Peserta yang belum melakukan presensi masuk.
     * Syarat:
     * - Hari kerja resmi (bukan libur/weekend, kecuali working_day override).
     * - Jika tanggal = hari ini: waktu server (WIB) sudah mencapai atau melewati cutoff presensi EWS (default 09:00).
     *   Sebelum jam cutoff, hasilnya kosong/belum terpicu.
     * - Jika tanggal < hari ini (historis): cutoff hari tersebut dianggap telah berlalu.
     * - Jika tanggal > hari ini (masa depan): tidak memicu alarm harian.
     * - Peserta aktif (role = intern, is_active = true).
     * - Tidak memiliki izin/sakit yang sudah disetujui (excused).
     * - Belum memiliki record presensi berstatus selain rejected.
     */
    public function getUnsubmittedAttendance(?string $date = null, ?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');
        $today = $now->format('Y-m-d');

        // Jika hari libur / akhir pekan (tanpa override working_day), tidak ada kewajiban presensi
        if (! $this->calendarService->isWorkingDay($targetDate)) {
            return collect();
        }

        // Tanggal di masa depan tidak memicu indikator harian
        if ($targetDate > $today) {
            return collect();
        }

        // Jika tanggal adalah hari ini, cek apakah jam cutoff sudah terlewati
        if ($targetDate === $today) {
            if ($now->format('H:i') < $this->getCutoffPresensiTime()) {
                return collect();
            }
        }

        // Ambil peserta magang aktif sesuai cakupan (semua untuk Admin, atau bimbingan aktif untuk PIC)
        $interns = $this->getTargetInterns($picUserId);
        $warnings = collect();

        foreach ($interns as $intern) {
            // Kecualikan jika peserta memiliki izin/sakit yang sudah disetujui
            if ($this->recapService->isInternExcusedOnDate($intern->id, $targetDate)) {
                continue;
            }

            // Periksa apakah peserta sudah presensi sah (bukan rejected)
            $hasValidAttendance = Attendance::where('intern_user_id', $intern->id)
                ->whereDate('attendance_date', $targetDate)
                ->where('verification_status', '!=', 'rejected')
                ->exists();

            if (! $hasValidAttendance) {
                $warnings->push([
                    'type' => 'unsubmitted_attendance',
                    'badge' => 'BELUM PRESENSI',
                    'level' => 'danger',
                    'intern' => $intern,
                    'title' => 'Belum Presensi Masuk',
                    'detail' => 'Belum melakukan check-in melewati batas cutoff EWS ' . $this->getCutoffPresensiTime() . ' WIB.',
                    'date' => $targetDate,
                ]);
            }
        }

        return $warnings;
    }

    /**
     * Indikator 2: Peserta yang belum mengisi logbook harian manual.
     * Syarat:
     * - Hari kerja resmi (bukan libur/weekend, kecuali working_day override).
     * - Jika tanggal = hari ini: waktu server (WIB) sudah mencapai atau melewati cutoff logbook EWS (default 17:00).
     *   Sebelum jam cutoff, hasilnya kosong/belum terpicu.
     * - Jika tanggal < hari ini (historis): cutoff hari tersebut dianggap telah berlalu.
     * - Jika tanggal > hari ini (masa depan): tidak memicu alarm harian.
     * - Peserta aktif (role = intern, is_active = true).
     * - Tidak memiliki izin/sakit yang sudah disetujui (excused).
     * - Belum membuat logbook dengan source = 'manual' pada tanggal bersangkutan.
     */
    public function getMissingLogbooks(?string $date = null, ?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');
        $today = $now->format('Y-m-d');

        // Jika hari libur / akhir pekan, tidak ada kewajiban logbook harian
        if (! $this->calendarService->isWorkingDay($targetDate)) {
            return collect();
        }

        // Tanggal di masa depan tidak memicu indikator harian
        if ($targetDate > $today) {
            return collect();
        }

        // Jika tanggal adalah hari ini, cek apakah jam cutoff sudah terlewati
        if ($targetDate === $today) {
            if ($now->format('H:i') < $this->getCutoffLogbookTime()) {
                return collect();
            }
        }

        $interns = $this->getTargetInterns($picUserId);
        $warnings = collect();

        foreach ($interns as $intern) {
            if ($this->recapService->isInternExcusedOnDate($intern->id, $targetDate)) {
                continue;
            }

            // Hanya logbook manual yang dihitung sebagai pemenuhan kewajiban peserta
            $hasManualLogbook = Logbook::manual()
                ->where('intern_user_id', $intern->id)
                ->whereDate('log_date', $targetDate)
                ->exists();

            if (! $hasManualLogbook) {
                $warnings->push([
                    'type' => 'missing_logbook',
                    'badge' => 'LOGBOOK KOSONG',
                    'level' => 'warning',
                    'intern' => $intern,
                    'title' => 'Logbook Harian Belum Diisi',
                    'detail' => 'Belum mencatat aktivitas logbook manual melewati batas cutoff EWS ' . $this->getCutoffLogbookTime() . ' WIB.',
                    'date' => $targetDate,
                ]);
            }
        }

        return $warnings;
    }

    /**
     * Indikator 3: Tugas yang sudah melewati tenggat waktu (overdue) dan belum accepted.
     * Hanya mengevaluasi tugas milik peserta magang aktif (is_active = true).
     */
    public function getOverdueTasks(?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $query = Task::overdue()
            ->whereHas('intern', fn ($q) => $q->where('is_active', true))
            ->with(['intern', 'pic']);

        if ($picUserId !== null) {
            $query->where('pic_user_id', $picUserId);
        }

        $tasks = $query->get();

        return $tasks->map(function (Task $task) use ($now) {
            $hoursLate = (int) $task->deadline_at?->diffInHours($now);
            $daysLate = (int) $task->deadline_at?->diffInDays($now);
            $lateText = $daysLate > 0 ? "Terlambat {$daysLate} hari" : "Terlambat {$hoursLate} jam";

            return [
                'type' => 'overdue_task',
                'badge' => 'TUGAS OVERDUE',
                'level' => 'danger',
                'intern' => $task->intern,
                'task' => $task,
                'title' => $task->title,
                'detail' => "Tenggat waktu berakhir {$task->deadline_at?->translatedFormat('d M Y, H:i')} WIB ({$lateText}).",
                'deadline_at' => $task->deadline_at,
            ];
        });
    }

    /**
     * Ringkasan seluruh sinyal EWS untuk Halaman & Dashboard Admin / HR.
     */
    public function getAdminEwsSummary(?string $date = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');

        $unsubmitted = $this->getUnsubmittedAttendance($targetDate, null);
        $missingLogbooks = $this->getMissingLogbooks($targetDate, null);
        $overdueTasks = $this->getOverdueTasks(null);

        $allWarnings = $overdueTasks->concat($unsubmitted)->concat($missingLogbooks);

        return [
            'target_date' => $targetDate,
            'unsubmitted_attendance_count' => $unsubmitted->count(),
            'missing_logbook_count' => $missingLogbooks->count(),
            'overdue_tasks_count' => $overdueTasks->count(),
            'total_warning_count' => $allWarnings->count(),
            'warnings_list' => $allWarnings->values(),
            'cutoff_attendance' => $this->getCutoffPresensiTime(),
            'cutoff_logbook' => $this->getCutoffLogbookTime(),
        ];
    }

    /**
     * Ringkasan sinyal EWS khusus cakupan peserta & tugas PIC yang sedang login.
     */
    public function getPicEwsSummary(int $picUserId, ?string $date = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');

        $unsubmitted = $this->getUnsubmittedAttendance($targetDate, $picUserId);
        $missingLogbooks = $this->getMissingLogbooks($targetDate, $picUserId);
        $overdueTasks = $this->getOverdueTasks($picUserId);

        $allWarnings = $overdueTasks->concat($unsubmitted)->concat($missingLogbooks);

        return [
            'target_date' => $targetDate,
            'unsubmitted_attendance_count' => $unsubmitted->count(),
            'missing_logbook_count' => $missingLogbooks->count(),
            'overdue_tasks_count' => $overdueTasks->count(),
            'total_warning_count' => $allWarnings->count(),
            'warnings_list' => $allWarnings->values(),
            'cutoff_attendance' => $this->getCutoffPresensiTime(),
            'cutoff_logbook' => $this->getCutoffLogbookTime(),
        ];
    }

    /**
     * Mengambil daftar peserta magang aktif sesuai peran peninjau.
     */
    protected function getTargetInterns(?int $picUserId = null): Collection
    {
        if ($picUserId === null) {
            return User::where('role', 'intern')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $assignedInternIds = PicAssignment::active()
            ->where('pic_user_id', $picUserId)
            ->pluck('intern_user_id')
            ->unique()
            ->toArray();

        if (empty($assignedInternIds)) {
            return collect();
        }

        return User::whereIn('id', $assignedInternIds)
            ->where('role', 'intern')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
