<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public User $admin
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
        $isApproved = $this->leaveRequest->isApproved();
        $typeLabel = $this->leaveRequest->isSick() ? 'sakit' : 'izin';
        $dateLabel = $this->leaveRequest->start_date->translatedFormat('d M Y');
        $decisionLabel = $isApproved ? 'disetujui' : 'ditolak';
        $noteSuffix = $this->leaveRequest->review_notes ? " Catatan: {$this->leaveRequest->review_notes}" : '';

        return [
            'title' => $isApproved ? 'Pengajuan Izin/Sakit Disetujui' : 'Pengajuan Izin/Sakit Ditolak',
            'message' => "Pengajuan {$typeLabel} ({$dateLabel}) {$decisionLabel} oleh {$this->admin->name}.{$noteSuffix}",
            'category' => 'leave',
            'type' => $isApproved ? 'info' : 'danger',
            'action_url' => route('intern.leave.index'),
            'actor_name' => $this->admin->name,
            'occurred_at' => now(config('wantrace.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
        ];
    }
}
