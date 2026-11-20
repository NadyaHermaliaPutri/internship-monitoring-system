<?php

namespace App\Services;

use App\Enums\LogbookSource;
use App\Enums\TaskStatus;
use App\Models\Attendance;
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
        protected AttendanceRecapService $recapService
    ) {}

    /**
     * Jam batas toleransi presensi masuk (WIB). Default: 08:05.
     */
    public function getCutoffPresensiTime(): string
    {
        return config('wantrace.work_hours.tolerance_cutoff', '08:05');
    }

    /**
     * Jam batas pengisian logbook harian (WIB). Default: 17:00.
     */
    public function getCutoffLogbookTime(): string
    {
        return config('wantrace.work_hours.end', '17:00');
    }

    /**
     * Indikator 1: Peserta yang belum melakukan presensi masuk.
     * Syarat:
     * - Hari kerja resmi (bukan libur/weekend).
     * - Waktu server sudah melewati cutoff presensi (08:05 WIB).
     * - Peserta aktif (role = intern, is_active = true).
     * - Tidak memiliki izin/sakit yang sudah disetujui (excused).
     * - Belum memiliki record presensi berstatus selain rejected.
     */
    public function getUnsubmittedAttendance(?string $date = null, ?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');

        // Jika hari libur / akhir pekan, tidak ada kewajiban presensi
        if (! $this->calendarService->isWorkingDay($targetDate)) {
            return collect();
        }

        // Jika tanggal adalah hari ini, cek apakah jam cutoff sudah terlewati
        if ($targetDate === $now->format('Y-m-d')) {
            $cutoff = Carbon::parse($targetDate . ' ' . $this->getCutoffPresensiTime() . ':59', $timezone);
            if ($now->lessThanOrEqualTo($cutoff)) {
                return collect();
            }
        } elseif ($targetDate > $now->format('Y-m-d')) {
            // Tanggal di masa depan tidak dievaluasi
            return collect();
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
                    'detail' => 'Belum melakukan check-in melewati batas toleransi ' . $this->getCutoffPresensiTime() . ' WIB.',
                    'date' => $targetDate,
                ]);
            }
        }

        return $warnings;
    }

    /**
     * Indikator 2: Peserta yang belum mengisi logbook harian manual.
     * Syarat:
     * - Hari kerja resmi (bukan libur/weekend).
     * - Waktu server sudah melewati cutoff logbook (17:00 WIB).
     * - Peserta aktif (role = intern, is_active = true).
     * - Tidak memiliki izin/sakit yang sudah disetujui (excused).
     * - Belum membuat logbook dengan source = 'manual' pada tanggal bersangkutan.
     */
    public function getMissingLogbooks(?string $date = null, ?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $targetDate = $date ?? $now->format('Y-m-d');

        // Jika hari libur / akhir pekan, tidak ada kewajiban logbook harian
        if (! $this->calendarService->isWorkingDay($targetDate)) {
            return collect();
        }

        // Jika tanggal adalah hari ini, cek apakah jam cutoff sudah terlewati
        if ($targetDate === $now->format('Y-m-d')) {
            $cutoff = Carbon::parse($targetDate . ' ' . $this->getCutoffLogbookTime() . ':00', $timezone);
            if ($now->lessThanOrEqualTo($cutoff)) {
                return collect();
            }
        } elseif ($targetDate > $now->format('Y-m-d')) {
            return collect();
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
                    'detail' => 'Belum mencatat aktivitas logbook manual melewati jam operasional ' . $this->getCutoffLogbookTime() . ' WIB.',
                    'date' => $targetDate,
                ]);
            }
        }

        return $warnings;
    }

    /**
     * Indikator 3: Tugas yang sudah melewati tenggat waktu (overdue) dan belum accepted.
     */
    public function getOverdueTasks(?int $picUserId = null): Collection
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $query = Task::overdue()->with(['intern', 'pic']);

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
     * Ringkasan seluruh sinyal EWS untuk Dashboard Admin / HR.
     */
    public function getAdminEwsSummary(?string $date = null): array
    {
        $unsubmitted = $this->getUnsubmittedAttendance($date, null);
        $missingLogbooks = $this->getMissingLogbooks($date, null);
        $overdueTasks = $this->getOverdueTasks(null);

        $allWarnings = $overdueTasks->concat($unsubmitted)->concat($missingLogbooks);

        return [
            'unsubmitted_attendance_count' => $unsubmitted->count(),
            'missing_logbook_count' => $missingLogbooks->count(),
            'overdue_tasks_count' => $overdueTasks->count(),
            'total_warning_count' => $allWarnings->count(),
            'warnings_list' => $allWarnings->values(),
        ];
    }

    /**
     * Ringkasan sinyal EWS khusus cakupan peserta & tugas PIC yang sedang login.
     */
    public function getPicEwsSummary(int $picUserId, ?string $date = null): array
    {
        $unsubmitted = $this->getUnsubmittedAttendance($date, $picUserId);
        $missingLogbooks = $this->getMissingLogbooks($date, $picUserId);
        $overdueTasks = $this->getOverdueTasks($picUserId);

        $allWarnings = $overdueTasks->concat($unsubmitted)->concat($missingLogbooks);

        return [
            'unsubmitted_attendance_count' => $unsubmitted->count(),
            'missing_logbook_count' => $missingLogbooks->count(),
            'overdue_tasks_count' => $overdueTasks->count(),
            'total_warning_count' => $allWarnings->count(),
            'warnings_list' => $allWarnings->values(),
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
