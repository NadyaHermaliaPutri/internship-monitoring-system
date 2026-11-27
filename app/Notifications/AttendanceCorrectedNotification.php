<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceCorrectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Attendance $attendance,
        public User $admin,
        public string $newStatus,
        public ?string $notes = null
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
        $statusLabel = $this->newStatus === 'on_time' ? 'Tepat Waktu' : 'Terlambat';
        $noteSuffix = $this->notes ? " Catatan: {$this->notes}" : '';

        return [
            'title' => 'Status Presensi Dikoreksi HR',
            'message' => "Status presensi tanggal {$dateLabel} diubah menjadi {$statusLabel} oleh {$this->admin->name}.{$noteSuffix}",
            'category' => 'attendance',
            'type' => 'info',
            'action_url' => route('intern.attendance.index'),
            'actor_name' => $this->admin->name,
            'occurred_at' => now(config('wantrace.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
        ];
    }
}
