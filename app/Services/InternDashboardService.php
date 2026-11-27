<?php

namespace App\Services;

use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class InternDashboardService
{
    public function __construct(
        private readonly HeaderNotificationService $headerNotificationService
    ) {}

    /**
     * Mengambil ringkasan data asli Modul 3 (Tugas, Logbook, Notifikasi) untuk Dashboard Peserta.
     *
     * @return array<string, mixed>
     */
    public function getSummary(User $intern): array
    {
        $now = Carbon::now(Task::TIMEZONE);
        $today = $now->toDateString();
        $approachingCutoff = $now->copy()->addHours(24);

        $activeTasksCount = 0;
        $overdueTasksCount = 0;
        $approachingTasksCount = 0;
        $upcomingTasks = collect();

        if (Schema::hasTable('tasks')) {
            $activeBase = Task::query()
                ->where('intern_user_id', $intern->id)
                ->where('status', '!=', TaskStatus::Accepted->value);

            $activeTasksCount = (clone $activeBase)->count();
            $overdueTasksCount = (clone $activeBase)->overdue($now)->count();
            $approachingTasksCount = (clone $activeBase)
                ->whereNotNull('deadline_at')
                ->where('deadline_at', '>=', $now->toDateTimeString())
                ->where('deadline_at', '<=', $approachingCutoff->toDateTimeString())
                ->count();

            $upcomingTasks = (clone $activeBase)
                ->with('pic:id,name')
                ->orderByRaw('CASE WHEN deadline_at IS NULL THEN 1 ELSE 0 END, deadline_at ASC')
                ->limit(5)
                ->get();
        }

        $todayLogbooksCount = 0;
        $pendingLogbooksCount = 0;
        $revisionLogbooksCount = 0;

        if (Schema::hasTable('logbooks')) {
            $manualBase = Logbook::query()
                ->manual()
                ->where('intern_user_id', $intern->id);

            $todayLogbooksCount = (clone $manualBase)->whereDate('log_date', $today)->count();
            $pendingLogbooksCount = (clone $manualBase)->where('status', LogbookStatus::Pending->value)->count();
            $revisionLogbooksCount = (clone $manualBase)->where('status', LogbookStatus::RevisionRequested->value)->count();
        }

        return [
            'active_tasks_count' => $activeTasksCount,
            'overdue_tasks_count' => $overdueTasksCount,
            'approaching_tasks_count' => $approachingTasksCount,
            'upcoming_tasks' => $upcomingTasks,
            'today_logbooks_count' => $todayLogbooksCount,
            'pending_logbooks_count' => $pendingLogbooksCount,
            'revision_logbooks_count' => $revisionLogbooksCount,
            'notifications' => $this->headerNotificationService->forUser($intern, 6),
        ];
    }
}
