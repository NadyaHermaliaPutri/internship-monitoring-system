<?php

namespace App\Services;

use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        protected WorkCalendarService $calendarService,
        protected AttendanceRecapService $recapService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Mengambil data rekap laporan lengkap berdasarkan jenis laporan dan filter.
     */
    public function getReportData(string $reportType, string $startDate, string $endDate, ?int $internId = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $selectedIntern = $internId ? User::where('id', $internId)->where('role', 'intern')->first() : null;
        $internScopeLabel = $selectedIntern ? $selectedIntern->name : 'Semua Peserta Magang';

        $workingDays = $this->calendarService->getWorkingDaysList($startDate, $endDate);
        $workingDaysCount = count($workingDays);

        $titles = [
            'attendance' => 'Rekapitulasi Presensi Peserta Magang',
            'leave' => 'Rekapitulasi Pengajuan Izin & Sakit',
            'logbook' => 'Rekapitulasi Logbook Harian Peserta',
            'task' => 'Rekapitulasi Penugasan & Progres Tugas',
            'monitoring' => 'Rekapitulasi Monitoring Evaluasi Peserta Magang',
        ];

        $reportTitle = $titles[$reportType] ?? 'Laporan Monitoring Evaluasi';

        $specificData = match ($reportType) {
            'attendance' => $this->getAttendanceReport($startDate, $endDate, $internId, $workingDaysCount),
            'leave' => $this->getLeaveReport($startDate, $endDate, $internId),
            'logbook' => $this->getLogbookReport($startDate, $endDate, $internId),
            'task' => $this->getTaskReport($startDate, $endDate, $internId),
            'monitoring' => $this->getMonitoringReport($startDate, $endDate, $internId, $workingDaysCount, $workingDays),
            default => [],
        };

        return array_merge($specificData, [
            'metadata' => [
                'report_type' => $reportType,
                'report_title' => $reportTitle,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'selected_intern' => $selectedIntern,
                'intern_id' => $internId,
                'intern_scope_label' => $internScopeLabel,
                'generated_at' => $now,
                'working_days_count' => $workingDaysCount,
            ],
        ]);
    }

    /**
     * 1. Rekapitulasi Presensi Peserta Magang.
     * Menggunakan Attendance::scopeValid() (verification_status != 'rejected')
     * agar presensi yang ditolak HR tidak dihitung sebagai hadir sah.
     */
    public function getAttendanceReport(string $startDate, string $endDate, ?int $internId = null, ?int $workingDaysCount = null): array
    {
        $workingDaysCount = $workingDaysCount ?? $this->calendarService->getWorkingDaysCount($startDate, $endDate);

        $query = Attendance::with(['intern', 'reviewer'])
            ->betweenDates($startDate, $endDate)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('intern_user_id', 'asc');

        if ($internId) {
            $query->where('intern_user_id', $internId);
        } else {
            $query->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $allRecords = $query->get();

        // Presensi sah: bukan rejected
        $validRecords = $allRecords->filter(fn (Attendance $a) => $a->isValidPresence());
        $onTimeRecords = $validRecords->filter(fn (Attendance $a) => $a->isOnTime());
        $lateRecords = $validRecords->filter(fn (Attendance $a) => $a->isLate());
        $rejectedRecords = $allRecords->filter(fn (Attendance $a) => $a->isRejected());

        // Hitung izin/sakit approved dalam rentang tanggal
        $leaveQuery = LeaveRequest::approved()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            });

        if ($internId) {
            $leaveQuery->where('intern_user_id', $internId);
        }

        $approvedLeavesCount = $leaveQuery->count();

        return [
            'attendances' => $allRecords,
            'summary' => [
                'working_days_count' => $workingDaysCount,
                'total_records' => $allRecords->count(),
                'total_valid_presence' => $validRecords->count(),
                'on_time_count' => $onTimeRecords->count(),
                'late_count' => $lateRecords->count(),
                'rejected_count' => $rejectedRecords->count(),
                'approved_leaves_count' => $approvedLeavesCount,
            ],
        ];
    }

    /**
     * 2. Rekapitulasi Pengajuan Izin / Sakit.
     */
    public function getLeaveReport(string $startDate, string $endDate, ?int $internId = null): array
    {
        $query = LeaveRequest::with(['intern', 'reviewer'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            })
            ->orderBy('start_date', 'desc');

        if ($internId) {
            $query->where('intern_user_id', $internId);
        } else {
            $query->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $records = $query->get();

        return [
            'leave_requests' => $records,
            'summary' => [
                'total_requests' => $records->count(),
                'approved_count' => $records->where('status', 'approved')->count(),
                'pending_count' => $records->where('status', 'pending')->count(),
                'rejected_count' => $records->where('status', 'rejected')->count(),
                'sick_count' => $records->where('type', 'sick')->count(),
                'leave_count' => $records->where('type', 'leave')->count(),
            ],
        ];
    }

    /**
     * 3. Rekapitulasi Logbook Harian Peserta.
     * Wajib Logbook::manual() (source = 'manual') agar catatan otomatis tidak tercampur.
     */
    public function getLogbookReport(string $startDate, string $endDate, ?int $internId = null): array
    {
        $query = Logbook::manual()
            ->with(['intern', 'reviewer'])
            ->withCount('revisions')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->orderBy('log_date', 'desc')
            ->orderBy('intern_user_id', 'asc');

        if ($internId) {
            $query->where('intern_user_id', $internId);
        } else {
            $query->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $records = $query->get();

        return [
            'logbooks' => $records,
            'summary' => [
                'total_entries' => $records->count(),
                'approved_count' => $records->where('status', LogbookStatus::Approved)->count(),
                'pending_count' => $records->where('status', LogbookStatus::Pending)->count(),
                'revision_requested_count' => $records->where('status', LogbookStatus::RevisionRequested)->count(),
            ],
        ];
    }

    /**
     * 4. Rekapitulasi Penugasan & Progres Tugas.
     * Query langsung tabel tasks tanpa task_submissions / task_reviews.
     */
    public function getTaskReport(string $startDate, string $endDate, ?int $internId = null): array
    {
        $now = Carbon::now(config('wantrace.timezone', 'Asia/Jakarta'));

        $query = Task::with(['intern', 'pic', 'acceptedByUser'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assigned_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orWhereBetween('deadline_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('assigned_at', '<=', $endDate . ' 23:59:59')
                            ->where('deadline_at', '>=', $startDate . ' 00:00:00');
                    });
            })
            ->orderBy('assigned_at', 'desc');

        if ($internId) {
            $query->where('intern_user_id', $internId);
        } else {
            $query->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $records = $query->get();

        $overdueCount = $records->filter(fn (Task $t) => $t->isOverdue($now))->count();
        $acceptedCount = $records->where('status', TaskStatus::Accepted)->count();
        $inProgressCount = $records->where('status', TaskStatus::InProgress)->count();
        $submittedCount = $records->where('status', TaskStatus::Submitted)->count();

        return [
            'tasks' => $records,
            'summary' => [
                'total_tasks' => $records->count(),
                'accepted_count' => $acceptedCount,
                'in_progress_count' => $inProgressCount,
                'submitted_count' => $submittedCount,
                'overdue_count' => $overdueCount,
            ],
        ];
    }

    /**
     * 5. Rekapitulasi Monitoring Evaluasi Peserta Magang (Ringkasan Komposit per Peserta).
     * TIDAK ADA formula nilai akhir / skor / bobot apa pun.
     */
    public function getMonitoringReport(
        string $startDate,
        string $endDate,
        ?int $internId = null,
        ?int $workingDaysCount = null,
        ?array $workingDays = null
    ): array {
        $now = Carbon::now(config('wantrace.timezone', 'Asia/Jakarta'));
        $workingDaysCount = $workingDaysCount ?? $this->calendarService->getWorkingDaysCount($startDate, $endDate);
        $workingDays = $workingDays ?? $this->calendarService->getWorkingDaysList($startDate, $endDate);

        $internsQuery = User::where('role', 'intern')->orderBy('name', 'asc');

        if ($internId) {
            $internsQuery->where('id', $internId);
        }

        $interns = $internsQuery->get();

        $summaries = [];

        foreach ($interns as $intern) {
            // Presensi
            $attendances = Attendance::where('intern_user_id', $intern->id)
                ->betweenDates($startDate, $endDate)
                ->get();

            $validAttendances = $attendances->where('verification_status', '!=', 'rejected');
            $onTimeCount = $validAttendances->where('status', 'on_time')->count();
            $lateCount = $validAttendances->where('status', 'late')->count();
            $validPresenceCount = $validAttendances->count();
            $rejectedCount = $attendances->where('verification_status', 'rejected')->count();

            // Hari izin/sakit approved dalam hari kerja periode
            $approvedLeaves = LeaveRequest::approved()
                ->where('intern_user_id', $intern->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereDate('start_date', '<=', $endDate)
                        ->whereDate('end_date', '>=', $startDate);
                })
                ->get();

            $approvedLeaveDays = 0;
            foreach ($workingDays as $workingDate) {
                foreach ($approvedLeaves as $leave) {
                    if ($workingDate >= $leave->start_date->format('Y-m-d') && $workingDate <= $leave->end_date->format('Y-m-d')) {
                        $approvedLeaveDays++;
                        break;
                    }
                }
            }

            // Logbook manual
            $logbooks = Logbook::manual()
                ->where('intern_user_id', $intern->id)
                ->whereBetween('log_date', [$startDate, $endDate])
                ->get();

            $logbooksTotal = $logbooks->count();
            $logbooksApproved = $logbooks->where('status', LogbookStatus::Approved)->count();

            // Tugas
            $tasks = Task::where('intern_user_id', $intern->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('assigned_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->orWhereBetween('deadline_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->orWhere(function ($sub) use ($startDate, $endDate) {
                            $sub->where('assigned_at', '<=', $endDate . ' 23:59:59')
                                ->where('deadline_at', '>=', $startDate . ' 00:00:00');
                        });
                })
                ->get();

            $tasksTotal = $tasks->count();
            $tasksAccepted = $tasks->where('status', TaskStatus::Accepted)->count();
            $tasksInProgress = $tasks->where('status', TaskStatus::InProgress)->count();
            $tasksOverdue = $tasks->filter(fn (Task $t) => $t->isOverdue($now))->count();

            $summaries[] = [
                'intern' => $intern,
                'is_active' => (bool) $intern->is_active,
                'working_days_count' => $workingDaysCount,
                'valid_presence_count' => $validPresenceCount,
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'rejected_count' => $rejectedCount,
                'approved_leave_days' => $approvedLeaveDays,
                'logbooks_total' => $logbooksTotal,
                'logbooks_approved' => $logbooksApproved,
                'tasks_total' => $tasksTotal,
                'tasks_accepted' => $tasksAccepted,
                'tasks_in_progress' => $tasksInProgress,
                'tasks_overdue' => $tasksOverdue,
            ];
        }

        return [
            'intern_summaries' => $summaries,
            'summary' => [
                'total_interns' => count($summaries),
                'working_days_count' => $workingDaysCount,
            ],
        ];
    }
}
