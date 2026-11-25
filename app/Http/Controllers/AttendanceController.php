<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\RejectAttendanceRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceRecapService;
use App\Services\AttendanceService;
use App\Services\WorkCalendarService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected WorkCalendarService $calendarService,
        protected AttendanceRecapService $recapService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | SISI PESERTA MAGANG (INTERN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman presensi masuk & riwayat presensi untuk Peserta Magang.
     */
    public function internIndex(Request $request): View
    {
        $intern = $request->user();
        $now = $this->calendarService->getNow();
        $todayDate = $now->format('Y-m-d');

        // Status hari ini berdasarkan aturan kalender kerja
        $dayStatus = $this->calendarService->getDayStatus($todayDate);
        $isWorkingDay = $this->calendarService->isWorkingDay($todayDate);

        // Periksa apakah peserta memiliki izin/sakit yang sudah disetujui hari ini
        $approvedLeaveToday = $this->recapService->getApprovedLeaveOnDate($intern->id, $todayDate);

        // Periksa apakah peserta sudah melakukan check-in hari ini
        $todayAttendance = Attendance::with('photo')
            ->where('intern_user_id', $intern->id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        $isRejectedToday = ! is_null($todayAttendance) && $todayAttendance->isRejected();

        // Kondisi apakah form/kamera aktif untuk check-in:
        // Aktif jika presensi hari ini berstatus ditolak (prioritas utama foto ulang),
        // ATAU jika belum pernah check-in hari ini dan tidak sedang izin/sakit yang disetujui.
        $canCheckIn = $isWorkingDay && ($isRejectedToday || (is_null($approvedLeaveToday) && is_null($todayAttendance)));

        // Filter riwayat presensi
        $selectedMonth = (int) $request->input('month', $now->month);
        $selectedYear = (int) $request->input('year', $now->year);
        $statusFilter = $request->input('status');

        // Ambil riwayat presensi
        $attendanceQuery = Attendance::with(['photo', 'reviewer'])
            ->where('intern_user_id', $intern->id)
            ->orderByDesc('attendance_date');

        if ($request->filled('month')) {
            $attendanceQuery->whereMonth('attendance_date', $selectedMonth);
        }
        if ($request->filled('year')) {
            $attendanceQuery->whereYear('attendance_date', $selectedYear);
        }
        if ($request->filled('status') && in_array($statusFilter, ['on_time', 'late'], true)) {
            $attendanceQuery->where('status', $statusFilter);
        }

        // Ambil riwayat izin/sakit (semua status) untuk unified history
        $leaveQuery = \App\Models\LeaveRequest::with(['evidence', 'reviewer'])
            ->where('intern_user_id', $intern->id);

        if ($request->filled('month')) {
            $leaveQuery->where(function ($q) use ($selectedMonth) {
                $q->whereMonth('start_date', $selectedMonth)
                  ->orWhereMonth('end_date', $selectedMonth);
            });
        }
        if ($request->filled('year')) {
            $leaveQuery->where(function ($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                  ->orWhereYear('end_date', $selectedYear);
            });
        }

        // Gabungkan dan sort manual untuk unified history (paginate 5/page)
        $allAttendances = $attendanceQuery->get()->map(fn ($a) => ['type' => 'attendance', 'date' => $a->attendance_date, 'data' => $a]);
        $allLeaves      = $leaveQuery->get()->map(fn ($l) => ['type' => 'leave', 'date' => $l->created_at->toDateString(), 'data' => $l]);

        $unified = $allAttendances->merge($allLeaves)
            ->sortByDesc('date')
            ->values();

        $perPage     = 5;
        $currentPage = (int) $request->input('page', 1);
        $sliced      = $unified->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $unifiedHistory = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $unified->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Jaga backward-compat: variabel lama $attendances tidak dipakai di view baru
        $attendances = $unifiedHistory;

        // Rekapitulasi kehadiran bulanan peserta
        $monthlyRecap = $this->recapService->getInternMonthlyRecap($intern->id, $selectedYear, $selectedMonth);

        return view('intern.attendance.index', compact(
            'intern',
            'now',
            'todayDate',
            'dayStatus',
            'isWorkingDay',
            'approvedLeaveToday',
            'todayAttendance',
            'isRejectedToday',
            'canCheckIn',
            'unifiedHistory',
            'monthlyRecap',
            'selectedMonth',
            'selectedYear',
            'statusFilter'
        ));
    }

    /**
     * Memproses pengiriman check-in presensi selfie oleh Peserta Magang.
     */
    public function internStore(StoreAttendanceRequest $request): RedirectResponse
    {
        $intern = $request->user();

        try {
            $photoFile = $request->file('photo');
            $notes = $request->input('notes');

            $todayDate = $this->calendarService->getNow()->format('Y-m-d');
            $wasRejected = Attendance::where('intern_user_id', $intern->id)
                ->whereDate('attendance_date', $todayDate)
                ->where('verification_status', 'rejected')
                ->exists();

            $attendance = $this->attendanceService->checkIn($intern, $photoFile, $notes);

            $timeFormatted = $attendance->checked_in_at->format('H:i:s');
            $statusLabel = $attendance->isOnTime() ? 'Tepat Waktu' : 'Terlambat';

            $successMessage = $wasRejected
                ? 'Foto presensi ulang berhasil dikirim.'
                : "Presensi masuk berhasil dicatat pada pukul {$timeFormatted} WIB dengan status {$statusLabel}.";

            return redirect()
                ->route('intern.attendance.index')
                ->with('success', $successMessage);
        } catch (Exception $e) {
            return redirect()
                ->route('intern.attendance.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SISI ADMINISTRATOR / HR (ADMIN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan monitoring presensi seluruh peserta magang untuk Admin/HR.
     */
    public function adminIndex(Request $request): View
    {
        $now = $this->calendarService->getNow();
        $selectedDate = $request->input('date', $now->format('Y-m-d'));
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        // Ringkasan statistik hari tersebut
        $dailyRecap = $this->recapService->getDailyOverallRecap($selectedDate);

        // Query presensi peserta pada tanggal terpilih
        $attendanceQuery = Attendance::with(['intern', 'photo', 'reviewer'])
            ->whereDate('attendance_date', $selectedDate)
            ->orderByDesc('checked_in_at');

        if ($statusFilter && in_array($statusFilter, ['on_time', 'late'], true)) {
            $attendanceQuery->where('status', $statusFilter);
        }

        if ($search) {
            $attendanceQuery->whereHas('intern', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $attendances = $attendanceQuery->paginate(10)->withQueryString();

        return view('admin.attendance.index', compact(
            'selectedDate',
            'statusFilter',
            'search',
            'dailyRecap',
            'attendances',
            'now'
        ));
    }

    /**
     * Memproses koreksi / peninjauan status presensi peserta oleh Admin/HR.
     */
    public function adminUpdate(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $admin = $request->user();

        Gate::authorize('update', $attendance);

        $validated = $request->validated();

        $this->attendanceService->reviewOrCorrectAttendance(
            attendance: $attendance,
            admin: $admin,
            newStatus: $validated['status'],
            reviewNotes: $validated['notes']
        );

        return redirect()
            ->back()
            ->with('success', 'Status presensi peserta berhasil diperbarui.');
    }

    /**
     * Memproses penolakan presensi peserta oleh Admin/HR.
     */
    public function adminReject(RejectAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $admin = $request->user();

        Gate::authorize('reject', $attendance);

        $validated = $request->validated();

        $this->attendanceService->rejectAttendance(
            attendance: $attendance,
            admin: $admin,
            reason: $validated['notes'],
            ipAddress: $request->ip()
        );

        $todayDate = $this->calendarService->getNow()->format('Y-m-d');
        $isToday = $attendance->attendance_date->format('Y-m-d') === $todayDate;
        $message = $isToday
            ? 'Presensi peserta berhasil ditolak. Akses kamera peserta untuk hari ini telah dibuka kembali untuk foto ulang.'
            : 'Presensi peserta berhasil ditolak.';

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | SISI PEMBIMBING PIC (READ-ONLY)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan monitoring read-only presensi peserta yang aktif dibimbing oleh PIC.
     */
    public function picIndex(Request $request): View
    {
        $pic = $request->user();
        $now = $this->calendarService->getNow();
        $selectedDate = $request->input('date', $now->format('Y-m-d'));

        // Ambil data presensi peserta yang saat ini aktif ditugaskan ke PIC ini (end_date IS NULL)
        $summary = $this->recapService->getPicAssignedInternsDailySummary($pic->id, $selectedDate);

        return view('pic.attendance.index', compact(
            'selectedDate',
            'summary',
            'now'
        ));
    }
}
