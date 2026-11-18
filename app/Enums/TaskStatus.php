<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case RevisionRequested = 'revision_requested';
    case Resubmitted = 'resubmitted';
    case Accepted = 'accepted';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Ditugaskan',
            self::InProgress => 'Sedang Dikerjakan',
            self::Submitted => 'Menunggu Review',
            self::RevisionRequested => 'Perlu Revisi',
            self::Resubmitted => 'Dikumpulkan Ulang',
            self::Accepted => 'Diterima (Selesai)',
        };
    }

    public function isAccepted(): bool
    {
        return $this === self::Accepted;
    }

    public function canBeEditedByPic(): bool
    {
        return $this !== self::Accepted;
    }
}
