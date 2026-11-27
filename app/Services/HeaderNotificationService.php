<?php

namespace App\Services;

use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class HeaderNotificationService
{
    /**
     * Mengambil ringkasan notifikasi real-time (gabungan Database Notifications & pengingat Modul 3) sesuai role pengguna.
     *
     * @return array{count: int, unread_db_count: int, items: array<int, array<string, mixed>>}
     */
    public function forUser(User $user, int $limit = 8): array
    {
        $dbItems = [];
        $unreadDbCount = 0;

        if (Schema::hasTable('notifications')) {
            $unreadDbCount = $user->unreadNotifications()->count();
            $dbNotifications = $user->unreadNotifications()
                ->latest()
                ->limit($limit)
                ->get();

            foreach ($dbNotifications as $notif) {
                $data = $notif->data ?? [];
                $dbItems[] = [
                    'id' => $notif->id,
                    'is_database' => true,
                    'type' => $data['type'] ?? 'info',
                    'title' => $data['title'] ?? 'Notifikasi',
                    'message' => $data['message'] ?? '',
                    'url' => $data['action_url'] ?? route('notifications.index'),
                    'meta' => $notif->created_at
                        ? $notif->created_at->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i') . ' WIB'
                        : null,
                ];
            }
        }

        $computedItems = match ($user->role) {
            'intern' => $this->forIntern($user),
            'pic' => $this->forPic($user),
            'admin' => $this->forAdmin(),
            default => [],
        };

        $items = array_merge($dbItems, $computedItems);

        return [
            'count' => count($items),
            'unread_db_count' => $unreadDbCount,
            'items' => array_slice($items, 0, $limit),
        ];
    }

    /**
     * @return array<int, array{type: string, title: string, message: string, url: string, meta: ?string}>
     */
    private function forIntern(User $intern): array
    {
        $items = [];
        $now = Carbon::now(Task::TIMEZONE);
        $approachingCutoff = $now->copy()->addHours(24);
        $seenTaskIds = [];

        if (Schema::hasTable('tasks')) {
            // 1. Tugas perlu revisi
            $revisionTasks = Task::query()
                ->with('pic:id,name')
                ->where('intern_user_id', $intern->id)
                ->where('status', TaskStatus::RevisionRequested->value)
                ->orderByDesc('updated_at')
                ->get();

            foreach ($revisionTasks as $task) {
                $seenTaskIds[$task->id] = true;
                $items[] = [
                    'type' => 'danger',
                    'title' => 'Tugas Perlu Revisi',
                    'message' => sprintf('"%s" diminta revisi oleh %s.', $task->title, $task->pic?->name ?? 'PIC'),
                    'url' => route('intern.tasks.show', $task),
                    'meta' => $task->deadline_at
                        ? 'Deadline: '.$task->deadline_at->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i')
                        : null,
                ];
            }

            // 2. Tugas aktif yang overdue, mendekati deadline (<= 24 jam), atau baru ditugaskan (assigned)
            $activeTasks = Task::query()
                ->with('pic:id,name')
                ->where('intern_user_id', $intern->id)
                ->where('status', '!=', TaskStatus::Accepted->value)
                ->orderByRaw('CASE WHEN deadline_at IS NULL THEN 1 ELSE 0 END, deadline_at ASC')
                ->get();

            foreach ($activeTasks as $task) {
                if (isset($seenTaskIds[$task->id])) {
                    continue;
                }

                $isOverdue = $task->isOverdue($now);
                $isApproaching = ! $isOverdue
                    && $task->deadline_at !== null
                    && $task->deadline_at->lte($approachingCutoff);

                if ($isOverdue) {
                    $items[] = [
                        'type' => 'danger',
                        'title' => 'Tugas Melewati Deadline',
                        'message' => sprintf('"%s" dari %s sudah melewati batas waktu.', $task->title, $task->pic?->name ?? 'PIC'),
                        'url' => route('intern.tasks.show', $task),
                        'meta' => 'Overdue • '.$task->deadline_at?->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i'),
                    ];
                } elseif ($isApproaching) {
                    $items[] = [
                        'type' => 'warning',
                        'title' => 'Mendekati Deadline',
                        'message' => sprintf('"%s" harus dikumpulkan sebelum %s.', $task->title, $task->deadline_at?->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i')),
                        'url' => route('intern.tasks.show', $task),
                        'meta' => 'Kurang dari 24 jam',
                    ];
                } elseif ($task->status === TaskStatus::Assigned) {
                    $items[] = [
                        'type' => 'info',
                        'title' => 'Tugas Baru dari PIC',
                        'message' => sprintf('%s memberikan tugas baru "%s".', $task->pic?->name ?? 'PIC', $task->title),
                        'url' => route('intern.tasks.show', $task),
                        'meta' => $task->deadline_at
                            ? 'Deadline: '.$task->deadline_at->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i')
                            : null,
                    ];
                }
            }
        }

        if (Schema::hasTable('logbooks')) {
            // 3. Logbook manual perlu revisi
            $revisionLogbooks = Logbook::query()
                ->manual()
                ->where('intern_user_id', $intern->id)
                ->where('status', LogbookStatus::RevisionRequested->value)
                ->orderByDesc('reviewed_at')
                ->orderByDesc('id')
                ->get();

            foreach ($revisionLogbooks as $logbook) {
                $items[] = [
                    'type' => 'danger',
                    'title' => 'Logbook Perlu Revisi',
                    'message' => sprintf('Catatan "%s" (%s) perlu diperbaiki.', $logbook->title, $logbook->log_date->translatedFormat('d M Y')),
                    'url' => route('intern.logbooks.show', $logbook),
                    'meta' => 'Logbook Harian',
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{type: string, title: string, message: string, url: string, meta: ?string}>
     */
    private function forPic(User $pic): array
    {
        $items = [];

        if (Schema::hasTable('tasks')) {
            $tasksAwaitingReview = Task::query()
                ->with('intern:id,name')
                ->where('pic_user_id', $pic->id)
                ->whereIn('status', [TaskStatus::Submitted->value, TaskStatus::Resubmitted->value])
                ->orderByDesc('updated_at')
                ->get();

            foreach ($tasksAwaitingReview as $task) {
                $items[] = [
                    'type' => 'warning',
                    'title' => 'Menunggu Review Tugas',
                    'message' => sprintf('%s mengumpulkan tugas "%s".', $task->intern?->name ?? 'Peserta', $task->title),
                    'url' => route('pic.tasks.show', $task),
                    'meta' => $task->status->label(),
                ];
            }
        }

        if (Schema::hasTable('logbooks')) {
            $pendingLogbooks = Logbook::query()
                ->with(['intern:id,name', 'relatedTask:id,pic_user_id'])
                ->manual()
                ->where('status', LogbookStatus::Pending->value)
                ->forPic($pic->id)
                ->orderByDesc('log_date')
                ->orderByDesc('id')
                ->get()
                ->filter(fn (Logbook $logbook) => $logbook->isReviewableByPic($pic->id));

            foreach ($pendingLogbooks as $logbook) {
                $items[] = [
                    'type' => 'info',
                    'title' => 'Menunggu Review Logbook',
                    'message' => sprintf('%s mengisi logbook "%s".', $logbook->intern?->name ?? 'Peserta', $logbook->title),
                    'url' => route('pic.logbooks.show', $logbook),
                    'meta' => $logbook->log_date->translatedFormat('d M Y'),
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{type: string, title: string, message: string, url: string, meta: ?string}>
     */
    private function forAdmin(): array
    {
        $items = [];

        if (Schema::hasTable('leave_requests')) {
            $pendingLeaves = LeaveRequest::query()
                ->with('intern:id,name')
                ->pending()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($pendingLeaves as $leave) {
                $typeLabel = $leave->isSick() ? 'Sakit' : 'Izin';
                $items[] = [
                    'type' => 'warning',
                    'title' => "Pengajuan {$typeLabel} Menunggu Review",
                    'message' => sprintf('%s mengajukan %s (%s).', $leave->intern?->name ?? 'Peserta', strtolower($typeLabel), $leave->start_date?->translatedFormat('d M Y') ?? '-'),
                    'url' => route('admin.leave.index'),
                    'meta' => 'Verifikasi Izin/Sakit',
                ];
            }
        }

        if (Schema::hasTable('tasks')) {
            $overdueTasks = Task::query()
                ->with('intern:id,name')
                ->overdue()
                ->orderBy('deadline_at')
                ->limit(5)
                ->get();

            foreach ($overdueTasks as $task) {
                $items[] = [
                    'type' => 'danger',
                    'title' => 'Tugas Melewati Deadline',
                    'message' => sprintf('Tugas "%s" (%s) melewati tenggat waktu.', $task->title, $task->intern?->name ?? 'Peserta'),
                    'url' => route('admin.tasks.show', $task),
                    'meta' => $task->deadline_at?->timezone(Task::TIMEZONE)->translatedFormat('d M Y, H:i'),
                ];
            }
        }

        return $items;
    }
}
