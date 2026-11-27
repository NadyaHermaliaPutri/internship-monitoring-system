<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public User $intern
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeLabel = $this->leaveRequest->isSick() ? 'Sakit' : 'Izin';
        $start = $this->leaveRequest->start_date->translatedFormat('d M Y');
        $end = $this->leaveRequest->end_date->translatedFormat('d M Y');
        $period = $start === $end ? $start : "{$start} - {$end}";

        return [
            'title' => "Pengajuan {$typeLabel} Baru",
            'message' => "{$this->intern->name} mengajukan {$typeLabel} untuk tanggal {$period}.",
            'category' => 'leave',
            'type' => 'warning',
            'action_url' => route('admin.leave.index'),
            'actor_name' => $this->intern->name,
            'occurred_at' => now(config('wantrace.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
        ];
    }
}
