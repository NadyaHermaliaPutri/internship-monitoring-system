<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PicAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceRecapService
{
    public function __construct(
        protected WorkCalendarService $calendarService
    ) {}

    /**
     * Memeriksa apakah seorang peserta magang memiliki izin/sakit yang sudah disetujui pada tanggal tertentu.
     * Service ini krusial untuk pengecualian EWS (Anggota 4) agar tidak mendeteksi izin sah sebagai ketidakhadiran.
     */
    public function isInternExcusedOnDate(int $internUserId, string $date): bool
    {
        return LeaveRequest::where('intern_user_id', $internUserId)
            ->activeOnDate($date)
            ->exists();
    }

    /**
     * Mendapatkan record izin/sakit yang disetujui pada tanggal tertentu jika ada.
     */
    public function getApprovedLeaveOnDate(int $internUserId, string $date): ?LeaveRequest
    {
        return LeaveRequest::where('intern_user_id', $internUserId)
            ->activeOnDate($date)
            ->first();
    }

    /**
     * Menghitung ringkasan rekapitulasi presensi harian untuk seluruh peserta magang (Dashboard Admin & Laporan).
     */
    public function getDailyOverallRecap(string $date): array
    {
        $dayStatus = $this->calendarService->getDayStatus($date);

        // Ambil semua peserta magang yang aktif
        $activeInternsCount = User::where('role', 'intern')->where('is_active', true)->count();

        // Presensi pada tanggal tersebut
        $attendances = Attendance::with(['intern', 'photo', 'reviewer'])
            ->whereDate('attendance_date', $date)
            ->get();

        // Presensi sah (bukan yang sedang berstatus rejected)
        $validAttendances = $attendances->where('verification_status', '!=', 'rejected');
        $onTimeCount = $validAttendances->where('status', 'on_time')->count();
        $lateCount = $validAttendances->where('status', 'late')->count();
        $totalPresent = $validAttendances->count();
        $rejectedCount = $attendances->where('verification_status', 'rejected')->count();

        // Izin/Sakit yang disetujui pada tanggal tersebut
        $approvedLeaves = LeaveRequest::with('intern')
            ->activeOnDate($date)
            ->get();

        $pendingLeavesCount = LeaveRequest::where('status', 'pending')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();

        return [
            'date' => $date,
            'day_status' => $dayStatus,
            'total_active_interns' => $activeInternsCount,
            'total_present' => $totalPresent,
            'on_time_count' => $onTimeCount,
            'late_count' => $lateCount,
            'rejected_count' => $rejectedCount,
            'rejected_count' => $rejectedCount,
            'approved_leaves_count' => $approvedLeaves->count(),
            'pending_leaves_count' => $pendingLeavesCount,
            'attendances' => $attendances,
            'approved_leaves' => $approvedLeaves,
        ];
    }

    /**
     * Menghitung rekapitulasi presensi bulanan seorang peserta magang.
     */
    public function getInternMonthlyRecap(int $internUserId, int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1, config('wantrace.timezone', 'Asia/Jakarta'))->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Daftar hari kerja resmi di bulan tersebut
        $workingDays = $this->calendarService->getWorkingDaysList($startDate, $endDate);
        $totalWorkingDays = count($workingDays);

        // Presensi peserta di bulan tersebut
        $attendances = Attendance::where('intern_user_id', $internUserId)
            ->whereDate('attendance_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('attendance_date', '<=', $endDate->format('Y-m-d'))
            ->get();

        $validAttendances = $attendances->where('verification_status', '!=', 'rejected');
        $onTimeCount = $validAttendances->where('status', 'on_time')->count();
        $lateCount = $validAttendances->where('status', 'late')->count();
        $totalPresent = $validAttendances->count();
        $rejectedCount = $attendances->where('verification_status', 'rejected')->count();

        // Izin/Sakit yang disetujui di bulan tersebut
        $approvedLeaves = LeaveRequest::where('intern_user_id', $internUserId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', '<=', $startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        $sickDaysCount = 0;
        $leaveDaysCount = 0;

        foreach ($approvedLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->max($startDate);
            $leaveEnd = Carbon::parse($leave->end_date)->min($endDate);
            $days = $this->calendarService->getWorkingDaysCount($leaveStart, $leaveEnd);

            if ($leave->isSick()) {
                $sickDaysCount += $days;
            } else {
                $leaveDaysCount += $days;
            }
        }

        $attendanceRate = $totalWorkingDays > 0 ? round(($totalPresent / $totalWorkingDays) * 100, 1) : 0;

        return [
            'intern_user_id' => $internUserId,
            'year' => $year,
            'month' => $month,
            'total_working_days' => $totalWorkingDays,
            'total_present' => $totalPresent,
            'on_time_count' => $onTimeCount,
            'late_count' => $lateCount,
            'rejected_count' => $rejectedCount,
            'sick_days_count' => $sickDaysCount,
            'leave_days_count' => $leaveDaysCount,
            'attendance_rate' => $attendanceRate,
            'attendances' => $attendances,
        ];
    }

    /**
     * Ringkasan presensi harian untuk peserta yang sedang dibimbing oleh PIC tertentu (read-only PIC).
     */
    public function getPicAssignedInternsDailySummary(int $picUserId, string $date): array
    {
        // Dapatkan ID peserta yang saat ini ditugaskan aktif ke PIC ini (end_date IS NULL)
        $assignedInternUserIds = PicAssignment::active()
            ->where('pic_user_id', $picUserId)
            ->pluck('intern_user_id')
            ->unique()
            ->toArray();

        if (empty($assignedInternUserIds)) {
            return [
                'date' => $date,
                'total_assigned_interns' => 0,
                'total_present' => 0,
                'attendances' => collect(),
                'approved_leaves' => collect(),
            ];
        }

        $attendances = Attendance::with(['intern', 'photo'])
            ->whereIn('intern_user_id', $assignedInternUserIds)
            ->whereDate('attendance_date', $date)
            ->get();

        $approvedLeaves = LeaveRequest::with('intern')
            ->whereIn('intern_user_id', $assignedInternUserIds)
            ->activeOnDate($date)
            ->get();

        $validAttendances = $attendances->where('verification_status', '!=', 'rejected');

        return [
            'date' => $date,
            'total_assigned_interns' => count($assignedInternUserIds),
            'total_present' => $validAttendances->count(),
            'on_time_count' => $validAttendances->where('status', 'on_time')->count(),
            'late_count' => $validAttendances->where('status', 'late')->count(),
            'rejected_count' => $attendances->where('verification_status', 'rejected')->count(),
            'attendances' => $attendances,
            'approved_leaves' => $approvedLeaves,
        ];
    }

    /**
     * Data rekap harian untuk Dashboard Peserta Magang (Intern).
     */
    public function getInternDailyDashboard(User $intern): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');
        $dayStatus = $this->calendarService->getDayStatus($today);

        // Presensi hari ini
        $attendanceToday = Attendance::with('photo')
            ->where('intern_user_id', $intern->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // Izin/Sakit yang disetujui hari ini
        $approvedLeaveToday = $this->getApprovedLeaveOnDate($intern->id, $today);

        // Jumlah izin pending milik peserta
        $pendingLeavesCount = LeaveRequest::where('intern_user_id', $intern->id)
            ->where('status', 'pending')
            ->count();

        // Izin terbaru milik peserta
        $latestLeave = LeaveRequest::with('evidence')
            ->where('intern_user_id', $intern->id)
            ->latest('id')
            ->first();

        // Rekap bulanan untuk persentase kehadiran
        $now = Carbon::now($timezone);
        $monthlyRecap = $this->getInternMonthlyRecap($intern->id, $now->year, $now->month);

        return [
            'date' => $today,
            'day_status' => $dayStatus,
            'attendance_today' => $attendanceToday,
            'is_rejected_today' => $attendanceToday?->isRejected() ?? false,
            'can_resubmit_today' => ($attendanceToday?->isRejected() ?? false) && ($dayStatus['is_working_day'] ?? true),
            'approved_leave_today' => $approvedLeaveToday,
            'pending_leaves_count' => $pendingLeavesCount,
            'latest_leave' => $latestLeave,
            'monthly_recap' => $monthlyRecap,
        ];
    }

    /**
     * Data rekap harian untuk Dashboard Admin / HR.
     */
    public function getAdminDailyDashboard(): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');
        $overallRecap = $this->getDailyOverallRecap($today);

        // Seluruh izin pending yang menunggu review HR
        $pendingLeavesCount = LeaveRequest::where('status', 'pending')->count();
        $pendingLeaves = LeaveRequest::with(['intern', 'evidence'])
            ->where('status', 'pending')
            ->latest('id')
            ->take(5)
            ->get();

        $overallRecap['pending_leaves_count'] = $pendingLeavesCount;
        $overallRecap['pending_leaves_list'] = $pendingLeaves;

        return $overallRecap;
    }

    /**
     * Data rekap harian untuk Dashboard Pembimbing PIC.
     */
    public function getPicDailyDashboard(User $pic): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');
        $dayStatus = $this->calendarService->getDayStatus($today);

        $assignedInternUserIds = $pic->picAssignments()
            ->active()
            ->pluck('intern_user_id')
            ->unique()
            ->values()
            ->toArray();

        $assignedInterns = User::whereIn('id', $assignedInternUserIds)
            ->where('role', 'intern')
            ->where('is_active', true)
            ->get();

        $assignedDailySummary = $this->getPicAssignedInternsDailySummary($pic->id, $today);

        $pendingLeaves = LeaveRequest::with(['intern', 'evidence'])
            ->whereIn('intern_user_id', $assignedInternUserIds)
            ->where('status', 'pending')
            ->latest('id')
            ->get();

        return array_merge($assignedDailySummary, [
            'day_status' => $dayStatus,
            'assigned_interns' => $assignedInterns,
            'pending_leaves' => $pendingLeaves,
        ]);
    }
}
