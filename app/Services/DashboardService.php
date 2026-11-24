<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected AttendanceRecapService $recapService,
        protected WorkCalendarService $calendarService,
        protected EwsService $ewsService
    ) {}

    /**
     * Mengumpulkan seluruh metrik dan ringkasan data untuk Dashboard Admin / HR.
     */
    public function getAdminDashboardData(?string $search = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $today = $now->format('Y-m-d');

        // 1. Rekap Harian Kehadiran & Kalender (menggunakan AttendanceRecapService)
        $adminSummary = $this->recapService->getAdminDailyDashboard();

        // Dukungan pencarian jika terdapat filter kata kunci nama/email peserta
        if (! empty($search)) {
            $keyword = strtolower(trim($search));
            $adminSummary['attendances'] = $adminSummary['attendances']->filter(function ($att) use ($keyword) {
                $name = strtolower($att->intern->name ?? '');
                $email = strtolower($att->intern->email ?? '');
                return str_contains($name, $keyword) || str_contains($email, $keyword);
            })->values();
        }

        // 2. Statistik Akun Pengguna
        $totalActiveInterns = User::where('role', 'intern')->where('is_active', true)->count();
        $totalActivePics    = User::where('role', 'pic')->where('is_active', true)->count();
        $totalActiveAdmins  = User::where('role', 'admin')->where('is_active', true)->count();
        $totalActive        = User::where('is_active', true)->count();
        $inactiveUsers      = User::where('is_active', false)->count();

        $stats = [
            'total_active_interns' => $totalActiveInterns,
            'intern_active'        => $totalActiveInterns,
            'total_active_pics'    => $totalActivePics,
            'pic_active'           => $totalActivePics,
            'total_active_admins'  => $totalActiveAdmins,
            'total_active'         => $totalActive,
            'inactive_users'       => $inactiveUsers,
        ];

        // 3. Ringkasan Tugas Magang Global
        $taskSummary = [
            'awaiting_review'    => Task::whereIn('status', [TaskStatus::Submitted, TaskStatus::Resubmitted])->count(),
            'in_progress'        => Task::whereIn('status', [TaskStatus::Assigned, TaskStatus::InProgress])->count(),
            'revision_requested' => Task::where('status', TaskStatus::RevisionRequested)->count(),
            'accepted'           => Task::where('status', TaskStatus::Accepted)->count(),
            'overdue'            => Task::overdue()->count(),
            'recent_tasks'       => Task::with(['intern:id,name,email', 'pic:id,name'])->latest('assigned_at')->take(5)->get(),
        ];

        // 4. Aktivitas Logbook Manual Terbaru
        $latestLogbooks = Logbook::manual()
            ->with(['intern:id,name,email', 'reviewer:id,name'])
            ->latest('id')
            ->take(5)
            ->get();

        // 5. Ringkasan Sinyal EWS Resmi
        $ewsSummary = $this->ewsService->getAdminEwsSummary($today);

        return [
            'adminSummary'   => $adminSummary,
            'stats'          => $stats,
            'taskSummary'    => $taskSummary,
            'latestLogbooks' => $latestLogbooks,
            'ewsSummary'     => $ewsSummary,
            'search'         => $search,
        ];
    }

    /**
     * Mengumpulkan data ringkasan khusus peserta dan tugas yang berada dalam cakupan PIC yang sedang login.
     */
    public function getPicDashboardData(User $pic, ?string $search = null): array
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);
        $today = $now->format('Y-m-d');

        // 1. Rekap Harian Bimbingan PIC (Presensi & Izin anak bimbingan via pic_assignments)
        $mentoringSummary = $this->recapService->getPicDailyDashboard($pic);

        // Filter pencarian peserta bimbingan jika ada keyword
        if (! empty($search)) {
            $keyword = strtolower(trim($search));
            $mentoringSummary['assigned_interns'] = $mentoringSummary['assigned_interns']->filter(function ($intern) use ($keyword) {
                return str_contains(strtolower($intern->name), $keyword) || str_contains(strtolower($intern->email), $keyword);
            })->values();
        }

        // 2. Statistik Tugas yang Dibuat oleh PIC Ini
        $activeTaskStatuses = [TaskStatus::Assigned, TaskStatus::InProgress];
        $allActiveOrReviewStatuses = [
            TaskStatus::Assigned,
            TaskStatus::InProgress,
            TaskStatus::Submitted,
            TaskStatus::RevisionRequested,
            TaskStatus::Resubmitted,
        ];

        $myActiveTasksQuery = Task::where('pic_user_id', $pic->id)->whereIn('status', $activeTaskStatuses);
        $myActiveTasksCount = (clone $myActiveTasksQuery)->count();
        $myAssignedInternsCount = (clone $myActiveTasksQuery)->distinct('intern_user_id')->count('intern_user_id');

        // Tugas mendekati tenggat (< 24 jam)
        $approachingDeadlineCount = Task::where('pic_user_id', $pic->id)
            ->whereIn('status', $activeTaskStatuses)
            ->whereNotNull('deadline_at')
            ->whereBetween('deadline_at', [$now, $now->copy()->addHours(24)])
            ->count();

        // Tugas butuh review Anda (submitted / resubmitted)
        $awaitingReviewCount = Task::where('pic_user_id', $pic->id)
            ->whereIn('status', [TaskStatus::Submitted, TaskStatus::Resubmitted])
            ->count();

        // Tugas dalam proses revisi
        $inRevisionCount = Task::where('pic_user_id', $pic->id)
            ->where('status', TaskStatus::RevisionRequested)
            ->count();

        // 3. Submisi Menunggu Review PIC Ini
        $awaitingReviewTasks = Task::where('pic_user_id', $pic->id)
            ->whereIn('status', [TaskStatus::Submitted, TaskStatus::Resubmitted])
            ->with(['intern.internProfile'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        // 4. Daftar Peserta dengan Tugas dari PIC Ini (Pool Bersama)
        $myTasksWithInterns = Task::where('pic_user_id', $pic->id)
            ->whereIn('status', $allActiveOrReviewStatuses)
            ->with(['intern.internProfile'])
            ->latest('assigned_at')
            ->get();

        $internsWithTasks = $myTasksWithInterns->groupBy('intern_user_id')->map(function (Collection $tasks) use ($pic, $allActiveOrReviewStatuses) {
            $intern = $tasks->first()->intern;
            $latestTask = $tasks->first();

            // Total beban kerja aktif peserta dari seluruh PIC
            $totalActiveLoad = Task::where('intern_user_id', $intern->id)
                ->whereIn('status', $allActiveOrReviewStatuses)
                ->count();

            // PIC lain yang juga memberikan tugas aktif pada peserta ini
            $otherPics = Task::where('intern_user_id', $intern->id)
                ->where('pic_user_id', '!=', $pic->id)
                ->whereIn('status', $allActiveOrReviewStatuses)
                ->with('pic:id,name')
                ->get()
                ->pluck('pic')
                ->filter()
                ->unique('id')
                ->values();

            return [
                'intern'             => $intern,
                'latest_task'        => $latestTask,
                'my_tasks_count'     => $tasks->count(),
                'total_active_tasks' => $totalActiveLoad,
                'other_pics'         => $otherPics,
            ];
        })->values();

        // 5. Sinyal Peringatan EWS Resmi Khusus Cakupan PIC
        $ewsSummary = $this->ewsService->getPicEwsSummary($pic->id, $today);

        // 6. Tenggat Tugas Mendatang Buatan PIC Ini
        $upcomingDeadlines = Task::where('pic_user_id', $pic->id)
            ->whereNotNull('deadline_at')
            ->where('status', '!=', TaskStatus::Accepted)
            ->with('intern:id,name')
            ->orderBy('deadline_at')
            ->take(5)
            ->get();

        return [
            'mentoringSummary' => $mentoringSummary,
            'taskStats' => [
                'active_tasks'             => $myActiveTasksCount,
                'assigned_interns_count'   => $myAssignedInternsCount,
                'approaching_deadline'     => $approachingDeadlineCount,
                'awaiting_review'          => $awaitingReviewCount,
                'in_revision'              => $inRevisionCount,
                'overdue'                  => $ewsSummary['overdue_tasks_count'],
            ],
            'awaitingReviewTasks' => $awaitingReviewTasks,
            'internsWithTasks'    => $internsWithTasks,
            'ewsSummary'          => $ewsSummary,
            'upcomingDeadlines'   => $upcomingDeadlines,
            'search'              => $search,
        ];
    }
}
