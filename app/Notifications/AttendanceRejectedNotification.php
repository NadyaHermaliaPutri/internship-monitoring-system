<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Attendance $attendance,
        public User $admin,
        public string $reason
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
        $dateLabel = $this->attendance->attendance_date->translatedFormat('d M Y');

        return [
            'title' => 'Foto Presensi Ditolak HR',
            'message' => "Presensi tanggal {$dateLabel} ditolak oleh {$this->admin->name}. Alasan: {$this->reason}",
            'category' => 'attendance',
            'type' => 'danger',
            'action_url' => route('intern.attendance.index'),
            'actor_name' => $this->admin->name,
            'occurred_at' => now(config('wantrace.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
        ];
    }
}
