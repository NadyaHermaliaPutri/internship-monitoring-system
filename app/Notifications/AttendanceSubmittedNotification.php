<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Attendance $attendance,
        public User $intern,
        public bool $isResubmission = false
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
        $checkedInWib = $this->attendance->checked_in_at
            ->timezone(config('wantrace.timezone', 'Asia/Jakarta'));
        $statusLabel = $this->attendance->isOnTime() ? 'Tepat Waktu' : 'Terlambat';

        return [
            'title' => $this->isResubmission ? 'Foto Presensi Ulang Dikirim' : 'Presensi Masuk Baru',
            'message' => $this->isResubmission
                ? "{$this->intern->name} mengirim foto presensi ulang ({$statusLabel})."
                : "{$this->intern->name} melakukan presensi masuk pukul {$checkedInWib->format('H:i')} WIB ({$statusLabel}).",
            'category' => 'attendance',
            'type' => 'info',
            'action_url' => route('admin.attendance.index', [
                'date' => $this->attendance->attendance_date->format('Y-m-d'),
            ]),
            'actor_name' => $this->intern->name,
            'occurred_at' => now(config('wantrace.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
        ];
    }
}
