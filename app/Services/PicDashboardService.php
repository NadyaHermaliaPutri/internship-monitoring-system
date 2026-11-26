<?php

namespace App\Services;

use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\InternProfile;
use App\Models\Logbook;
use App\Models\PicAssignment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PicDashboardService
{
    /**
     * Mengambil seluruh data asli Dashboard PIC secara ketat dalam cakupan PIC yang login.
     *
     * @return array<string, mixed>
     */
    public function getSummary(User $pic): array
    {
        $now = Carbon::now(Task::TIMEZONE);
        $approachingCutoff = $now->copy()->addHours(24);

        $activeTasksCount = 0;
        $activeInternsHoldingTasksCount = 0;
        $overdueTasksCount = 0;
        $approachingTasksCount = 0;
        $awaitingReviewTasksCount = 0;
        $revisionTasksCount = 0;

        $submissionsAwaitingReview = collect();
        $pendingLogbooks = collect();
        $pendingLogbooksCount = 0;
        $upcomingDeadlines = collect();
        $ewsSignals = [];
        $relatedInternRows = collect();

        if (Schema::hasTable('tasks')) {
            $picTasksBase = Task::query()->where('pic_user_id', $pic->id);
            $activeBase = (clone $picTasksBase)->where('status', '!=', TaskStatus::Accepted->value);

            $activeTasksCount = (clone $activeBase)->count();
            $activeInternsHoldingTasksCount = (clone $activeBase)->distinct('intern_user_id')->count('intern_user_id');
            $overdueTasksCount = (clone $activeBase)->overdue($now)->count();
            $approachingTasksCount = (clone $activeBase)
                ->whereNotNull('deadline_at')
                ->where('deadline_at', '>=', $now->toDateTimeString())
                ->where('deadline_at', '<=', $approachingCutoff->toDateTimeString())
                ->count();
            $awaitingReviewTasksCount = (clone $picTasksBase)
                ->whereIn('status', [TaskStatus::Submitted->value, TaskStatus::Resubmitted->value])
                ->count();
            $revisionTasksCount = (clone $picTasksBase)
                ->where('status', TaskStatus::RevisionRequested->value)
                ->count();

            // 1. Submisi Menunggu Review PIC
            $submissionsAwaitingReview = (clone $picTasksBase)
                ->with(['intern:id,name,email', 'latestSubmission.evidenceMedia'])
                ->whereIn('status', [TaskStatus::Submitted->value, TaskStatus::Resubmitted->value])
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();

            // 2. Tenggat Tugas Buatan PIC (maks 5 terdekat)
            $upcomingDeadlines = (clone $activeBase)
                ->with('intern:id,name')
                ->whereNotNull('deadline_at')
                ->orderBy('deadline_at')
                ->limit(5)
                ->get();

            // 3. Sinyal Peringatan (EWS) khusus tugas buatan PIC ini
            $overdueTasks = (clone $activeBase)
                ->with('intern:id,name')
                ->overdue($now)
                ->orderBy('deadline_at')
                ->limit(3)
                ->get();

            foreach ($overdueTasks as $task) {
                $daysLate = max(1, (int) $task->deadline_at->diffInDays($now));
                $ewsSignals[] = [
                    'type' => 'overdue',
                    'badge' => 'TUGAS OVERDUE',
                    'tag' => "Terlambat {$daysLate} Hari",
                    'title' => $task->title,
                    'intern_name' => $task->intern?->name ?? 'Peserta',
                    'detail' => 'Tenggat terlewat: '.$task->deadline_at->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i').' WIB',
                    'action_label' => 'Perpanjang Deadline',
                    'action_url' => route('pic.tasks.edit', $task),
                ];
            }

            $repeatedRevisionTasks = (clone $picTasksBase)
                ->with('intern:id,name')
                ->withCount('submissions')
                ->has('submissions', '>=', 2)
                ->where('status', TaskStatus::RevisionRequested->value)
                ->orderByDesc('updated_at')
                ->limit(2)
                ->get();

            foreach ($repeatedRevisionTasks as $task) {
                $ewsSignals[] = [
                    'type' => 'revision',
                    'badge' => "REVISI BERULANG ({$task->submissions_count}X)",
                    'tag' => 'Perlu Pendampingan',
                    'title' => $task->title,
                    'intern_name' => $task->intern?->name ?? 'Peserta',
                    'detail' => 'Peserta telah mengumpulkan '.$task->submissions_count.' kali dan masih berstatus Perlu Revisi.',
                    'action_label' => 'Lihat Detail Tugas',
                    'action_url' => route('pic.tasks.show', $task),
                ];
            }

            // 4. Peserta dengan Tugas dari PIC ini + Peserta Bimbingan Aktif (pic_assignments)
            $taskInternIds = (clone $activeBase)->pluck('intern_user_id');
            $assignedInternIds = Schema::hasTable('pic_assignments')
                ? PicAssignment::query()->active()->where('pic_user_id', $pic->id)->pluck('intern_user_id')
                : collect();

            $allRelatedInternIds = $taskInternIds->merge($assignedInternIds)->unique()->values();

            if ($allRelatedInternIds->isNotEmpty()) {
                $internUsers = User::query()
                    ->whereIn('id', $allRelatedInternIds)
                    ->where('role', 'intern')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email']);

                $institutions = Schema::hasTable('intern_profiles')
                    ? InternProfile::query()->whereIn('user_id', $allRelatedInternIds)->pluck('institution', 'user_id')
                    : collect();

                $picActiveTasksByIntern = (clone $activeBase)
                    ->whereIn('intern_user_id', $allRelatedInternIds)
                    ->orderByRaw('CASE WHEN deadline_at IS NULL THEN 1 ELSE 0 END, deadline_at ASC')
                    ->get()
                    ->groupBy('intern_user_id');

                $relatedInternRows = $internUsers->map(function (User $intern) use ($institutions, $picActiveTasksByIntern) {
                    $tasks = $picActiveTasksByIntern->get($intern->id, collect());

                    return [
                        'intern' => $intern,
                        'institution' => $institutions->get($intern->id) ?: '-',
                        'latest_task' => $tasks->first(),
                        'active_tasks_count' => $tasks->count(),
                    ];
                });
            }
        }

        // 5. Logbook Manual Menunggu Review PIC
        if (Schema::hasTable('logbooks')) {
            $reviewablePendingLogbooks = Logbook::query()
                ->with(['intern:id,name', 'relatedTask:id,pic_user_id'])
                ->manual()
                ->where('status', LogbookStatus::Pending->value)
                ->forPic($pic->id)
                ->orderByDesc('log_date')
                ->orderByDesc('id')
                ->get()
                ->filter(fn (Logbook $logbook) => $logbook->isReviewableByPic($pic->id))
                ->values();

            $pendingLogbooksCount = $reviewablePendingLogbooks->count();
            $pendingLogbooks = $reviewablePendingLogbooks->take(5);
        }

        // Institusi untuk daftar submisi menunggu review
        $submissionInternIds = $submissionsAwaitingReview->pluck('intern_user_id')->unique()->filter();
        $submissionInstitutions = ($submissionInternIds->isNotEmpty() && Schema::hasTable('intern_profiles'))
            ? InternProfile::query()->whereIn('user_id', $submissionInternIds)->pluck('institution', 'user_id')
            : collect();

        return [
            'active_tasks_count' => $activeTasksCount,
            'active_interns_count' => $activeInternsHoldingTasksCount,
            'overdue_tasks_count' => $overdueTasksCount,
            'approaching_tasks_count' => $approachingTasksCount,
            'awaiting_review_tasks_count' => $awaitingReviewTasksCount,
            'pending_logbooks_count' => $pendingLogbooksCount,
            'revision_tasks_count' => $revisionTasksCount,
            'submissions_awaiting_review' => $submissionsAwaitingReview,
            'submission_institutions' => $submissionInstitutions,
            'pending_logbooks' => $pendingLogbooks,
            'upcoming_deadlines' => $upcomingDeadlines,
            'ews_signals' => $ewsSignals,
            'related_interns' => $relatedInternRows,
            'related_interns_count' => $relatedInternRows->count(),
        ];
    }
}
