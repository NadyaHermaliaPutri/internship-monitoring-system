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
     * 4 jenis laporan resmi: attendance, leave, logbook, monitoring.
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
            'logbook' => 'Rekapitulasi Logbook & Tugas Magang',
            'monitoring' => 'Rekapitulasi Monitoring Evaluasi Peserta Magang',
        ];

        $reportTitle = $titles[$reportType] ?? 'Laporan Monitoring Evaluasi';

        $specificData = match ($reportType) {
            'attendance' => $this->getAttendanceReport($startDate, $endDate, $internId, $workingDaysCount, $workingDays),
            'leave' => $this->getLeaveReport($startDate, $endDate, $internId),
            'logbook' => $this->getLogbookReport($startDate, $endDate, $internId),
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
     * 1. Rekapitulasi Presensi Peserta Magang (Revisi 1):
     * - Hanya presensi sah/valid (verification_status != 'rejected'). Ditolak HR dikeluarkan sepenuhnya.
     * - Menampilkan baris per tanggal hari kerja sampai min(end_date, today) untuk peserta target.
     * - Jika tidak ada presensi sah:
     *   * Cek LeaveRequest approved (status: Izin / Sakit).
     *   * Jika tidak ada izin/sakit sah: status 'Tidak Hadir' (tanpa membuat record di database).
     * - Tanggal masa depan (> today) dalam periode TIDAK ditandai 'Tidak Hadir'.
     */
    public function getAttendanceReport(
        string $startDate,
        string $endDate,
        ?int $internId = null,
        ?int $workingDaysCount = null,
        ?array $workingDays = null
    ): array {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');

        $workingDays = $workingDays ?? $this->calendarService->getWorkingDaysList($startDate, $endDate);
        $workingDaysCount = $workingDaysCount ?? count($workingDays);

        // Hanya evaluasi hari kerja yang sudah atau sedang berjalan (<= today) untuk kehadiran 'Tidak Hadir'
        $evaluableWorkingDays = array_values(array_filter($workingDays, fn ($date) => $date <= $today));

        // Ambil peserta magang
        $internsQuery = User::where('role', 'intern')->orderBy('name', 'asc');
        if ($internId) {
            $internsQuery->where('id', $internId);
        }
        $interns = $internsQuery->get();

        // Eager-load presensi sah dalam rentang tanggal
        $attendancesQuery = Attendance::with(['intern', 'reviewer'])
            ->valid()
            ->betweenDates($startDate, $endDate);
        if ($internId) {
            $attendancesQuery->where('intern_user_id', $internId);
        } else {
            $attendancesQuery->whereIn('intern_user_id', $interns->pluck('id'));
        }
        $attendances = $attendancesQuery->get();

        // Group attendances by intern_user_id and attendance_date
        $attendancesGrouped = $attendances->groupBy(function (Attendance $att) {
            return $att->intern_user_id . '_' . $att->attendance_date?->format('Y-m-d');
        });

        // Eager-load izin/sakit approved dalam rentang tanggal
        $leaveQuery = LeaveRequest::approved()
            ->with(['intern', 'reviewer'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            });
        if ($internId) {
            $leaveQuery->where('intern_user_id', $internId);
        } else {
            $leaveQuery->whereIn('intern_user_id', $interns->pluck('id'));
        }
        $leaves = $leaveQuery->get();

        $rows = collect();
        $totalValidPresence = 0;
        $onTimeCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $approvedLeavesCount = 0;

        foreach ($evaluableWorkingDays as $dateStr) {
            $carbonDate = Carbon::parse($dateStr, $timezone);

            foreach ($interns as $intern) {
                $groupKey = $intern->id . '_' . $dateStr;
                $validAtt = $attendancesGrouped->get($groupKey)?->first();

                if ($validAtt) {
                    $totalValidPresence++;
                    if ($validAtt->isOnTime()) {
                        $onTimeCount++;
                        $statusLabel = 'Hadir';
                    } elseif ($validAtt->isLate()) {
                        $lateCount++;
                        $statusLabel = 'Terlambat';
                    } else {
                        $statusLabel = $validAtt->status;
                    }

                    $rows->push([
                        'date' => $carbonDate,
                        'intern' => $intern,
                        'intern_user_id' => $intern->id,
                        'time_in' => $validAtt->checked_in_at ? $validAtt->checked_in_at->format('H:i') . ' WIB' : '-',
                        'status' => $statusLabel,
                        'status_type' => $validAtt->isOnTime() ? 'success' : 'warning',
                        'verification_status' => $validAtt->verification_status,
                        'notes' => $validAtt->notes ?: '-',
                        'reviewer_name' => $validAtt->reviewer?->name,
                    ]);
                    continue;
                }

                // Cek izin/sakit sah
                $matchedLeave = $leaves->first(function (LeaveRequest $l) use ($intern, $dateStr) {
                    if ($l->intern_user_id !== $intern->id) {
                        return false;
                    }
                    $start = $l->start_date?->format('Y-m-d');
                    $end = $l->end_date?->format('Y-m-d');
                    return $start && $end && $dateStr >= $start && $dateStr <= $end;
                });

                if ($matchedLeave) {
                    $approvedLeavesCount++;
                    $isSick = $matchedLeave->isSick();
                    $rows->push([
                        'date' => $carbonDate,
                        'intern' => $intern,
                        'intern_user_id' => $intern->id,
                        'time_in' => '-',
                        'status' => $isSick ? 'Sakit' : 'Izin',
                        'status_type' => 'info',
                        'verification_status' => 'approved',
                        'notes' => $matchedLeave->reason ?: ($isSick ? 'Sakit dengan persetujuan HR' : 'Izin dengan persetujuan HR'),
                        'reviewer_name' => $matchedLeave->reviewer?->name,
                    ]);
                    continue;
                }

                // Tidak hadir (Alpa / tidak presensi sah pada hari kerja yang telah lewat)
                $absentCount++;
                $rows->push([
                    'date' => $carbonDate,
                    'intern' => $intern,
                    'intern_user_id' => $intern->id,
                    'time_in' => '-',
                    'status' => 'Tidak Hadir',
                    'status_type' => 'danger',
                    'verification_status' => '-',
                    'notes' => 'Tidak ada presensi sah atau izin/sakit pada hari kerja ini',
                    'reviewer_name' => null,
                ]);
            }
        }

        // Urutkan baris: tanggal terbaru di atas, lalu nama peserta
        $sortedRows = $rows->sort(function ($a, $b) {
            $dateCmp = strcmp($b['date']->format('Y-m-d'), $a['date']->format('Y-m-d'));
            if ($dateCmp !== 0) {
                return $dateCmp;
            }
            return strcmp($a['intern']->name, $b['intern']->name);
        })->values();

        return [
            'attendances' => $sortedRows,
            'summary' => [
                'working_days_count' => $workingDaysCount,
                'evaluable_days_count' => count($evaluableWorkingDays),
                'total_records' => $sortedRows->count(),
                'total_valid_presence' => $totalValidPresence,
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
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
     * 3. Rekapitulasi Gabungan Logbook & Tugas Magang (Revisi 2):
     * - Menggabungkan entri Logbook Manual (Logbook::manual()) dan Tugas PIC (Task).
     * - Kolom: No, Tanggal, Nama Peserta, Jenis ('Manual' / 'Tugas PIC'),
     *   Aktivitas / Kegiatan, Penanggung Jawab.
     * - Penanggung Jawab:
     *   * Untuk Tugas PIC: nama PIC pembuat tugas ($task->pic->name).
     *   * Untuk Manual: reviewer logbook atau fallback PIC pembimbing peserta dari pic_assignments.
     * - Diurutkan berdasarkan tanggal terbaru.
     */
    public function getLogbookReport(string $startDate, string $endDate, ?int $internId = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        // A. Logbook Manual
        $logbookQuery = Logbook::manual()
            ->with([
                'intern.internPicAssignments.pic',
                'reviewer',
            ])
            ->whereBetween('log_date', [$startDate, $endDate]);

        if ($internId) {
            $logbookQuery->where('intern_user_id', $internId);
        } else {
            $logbookQuery->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $logbookRecords = $logbookQuery->get();

        $combinedItems = collect();

        foreach ($logbookRecords as $lb) {
            // Tentukan penanggung jawab
            $picInCharge = '-';
            if ($lb->reviewer) {
                $picInCharge = $lb->reviewer->name;
            } else {
                $logDate = $lb->log_date ? $lb->log_date->format('Y-m-d') : null;
                $assignments = $lb->intern?->internPicAssignments ?? collect();

                if ($logDate) {
                    $matchedAssignment = $assignments->first(function ($assignment) use ($logDate) {
                        $start = $assignment->start_date ? Carbon::parse($assignment->start_date)->format('Y-m-d') : null;
                        $end = $assignment->end_date ? Carbon::parse($assignment->end_date)->format('Y-m-d') : null;
                        return (is_null($start) || $start <= $logDate) && (is_null($end) || $end >= $logDate);
                    });

                    if ($matchedAssignment && $matchedAssignment->pic) {
                        $picInCharge = $matchedAssignment->pic->name;
                    }
                }

                if ($picInCharge === '-') {
                    $activeAssignment = $assignments->first(fn ($a) => is_null($a->end_date));
                    if ($activeAssignment && $activeAssignment->pic) {
                        $picInCharge = $activeAssignment->pic->name;
                    }
                }
            }

            $combinedItems->push([
                'source_type' => 'manual',
                'type_label' => 'Manual',
                'date' => $lb->log_date,
                'intern' => $lb->intern,
                'intern_name' => $lb->intern?->name ?? 'Peserta #' . $lb->intern_user_id,
                'title' => $lb->title,
                'description' => $lb->activity_description,
                'status_label' => $lb->status?->label() ?? 'Manual',
                'status_value' => $lb->status?->value ?? 'manual',
                'pic_in_charge' => $picInCharge,
                'meta_info' => null,
            ]);
        }

        // B. Tugas PIC (Task)
        $taskQuery = Task::with([
            'intern',
            'pic',
            'latestSubmission.latestReview',
        ])
            ->withCount('submissions')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assigned_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orWhereBetween('deadline_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('assigned_at', '<=', $endDate . ' 23:59:59')
                            ->where('deadline_at', '>=', $startDate . ' 00:00:00');
                    });
            });

        if ($internId) {
            $taskQuery->where('intern_user_id', $internId);
        } else {
            $taskQuery->whereHas('intern', fn ($q) => $q->where('role', 'intern'));
        }

        $taskRecords = $taskQuery->get();

        foreach ($taskRecords as $task) {
            $isOverdue = $task->isOverdue($now);
            $latestSub = $task->latestSubmission;
            $latestRev = $latestSub?->latestReview;

            $metaInfo = [];
            if ($task->deadline_at) {
                $metaInfo[] = 'Deadline: ' . $task->deadline_at->format('d/m/Y H:i');
            }
            if ($task->submissions_count > 0 && $latestSub) {
                $subText = 'Submisi v' . $latestSub->submission_number . ' (' . $task->submissions_count . 'x)';
                if ($latestRev) {
                    $subText .= ' • Review: ' . ($latestRev->decision?->label() ?? $latestRev->decision?->value);
                }
                $metaInfo[] = $subText;
            }

            $combinedItems->push([
                'source_type' => 'task',
                'type_label' => 'Tugas PIC',
                'date' => $task->assigned_at ? $task->assigned_at->copy()->startOfDay() : ($task->deadline_at ? $task->deadline_at->copy()->startOfDay() : null),
                'intern' => $task->intern,
                'intern_name' => $task->intern?->name ?? 'Peserta #' . $task->intern_user_id,
                'title' => $task->title,
                'description' => $task->description,
                'status_label' => $isOverdue ? 'Overdue' : ($task->status?->label() ?? 'Tugas'),
                'status_value' => $isOverdue ? 'overdue' : ($task->status?->value ?? 'task'),
                'pic_in_charge' => $task->pic?->name ?? '-',
                'meta_info' => ! empty($metaInfo) ? implode(' • ', $metaInfo) : null,
            ]);
        }

        // Urutkan gabungan: tanggal terbaru di atas
        $sortedItems = $combinedItems->sort(function ($a, $b) {
            $dateA = $a['date'] ? $a['date']->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
            $dateB = $b['date'] ? $b['date']->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
            $cmp = strcmp($dateB, $dateA);
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcmp($a['intern_name'], $b['intern_name']);
        })->values();

        return [
            'entries' => $sortedItems,
            'summary' => [
                'total_items' => $sortedItems->count(),
                'manual_logbooks_count' => $logbookRecords->count(),
                'tasks_count' => $taskRecords->count(),
                'tasks_accepted_count' => $taskRecords->where('status', TaskStatus::Accepted)->count(),
                'tasks_overdue_count' => $taskRecords->filter(fn (Task $t) => $t->isOverdue($now))->count(),
            ],
        ];
    }

    /**
     * 4. Rekapitulasi Monitoring Evaluasi Peserta Magang (Revisi 3):
     * - Kehadiran hanya: Hadir Sah (valid_presence_count), Izin Sah, Sakit Sah.
     * - Hapus kolom Ditolak.
     * - Logbook Manual: hanya angka total jumlah logbook manual ($logbooksTotal).
     * - Ringkasan Tugas tetap ringkas (Total, Selesai, Overdue).
     * - Murni agregasi data faktual TANPA formula nilai akhir / skor.
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
            // Presensi Sah (Valid)
            $validPresenceCount = Attendance::valid()
                ->where('intern_user_id', $intern->id)
                ->betweenDates($startDate, $endDate)
                ->count();

            // Hari izin dan sakit approved dalam hari kerja periode
            $approvedLeaves = LeaveRequest::approved()
                ->where('intern_user_id', $intern->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereDate('start_date', '<=', $endDate)
                        ->whereDate('end_date', '>=', $startDate);
                })
                ->get();

            $approvedLeaveDays = 0;
            $approvedSickDays = 0;

            foreach ($workingDays as $workingDate) {
                foreach ($approvedLeaves as $leave) {
                    $start = $leave->start_date?->format('Y-m-d');
                    $end = $leave->end_date?->format('Y-m-d');
                    if ($start && $end && $workingDate >= $start && $workingDate <= $end) {
                        if ($leave->isSick()) {
                            $approvedSickDays++;
                        } else {
                            $approvedLeaveDays++;
                        }
                        break;
                    }
                }
            }

            // Logbook manual (cukup total angka saja)
            $logbooksTotal = Logbook::manual()
                ->where('intern_user_id', $intern->id)
                ->whereBetween('log_date', [$startDate, $endDate])
                ->count();

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
            $tasksOverdue = $tasks->filter(fn (Task $t) => $t->isOverdue($now))->count();

            $summaries[] = [
                'intern' => $intern,
                'is_active' => (bool) $intern->is_active,
                'working_days_count' => $workingDaysCount,
                'valid_presence_count' => $validPresenceCount,
                'approved_leave_days' => $approvedLeaveDays,
                'approved_sick_days' => $approvedSickDays,
                'logbooks_total' => $logbooksTotal,
                'tasks_total' => $tasksTotal,
                'tasks_accepted' => $tasksAccepted,
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
