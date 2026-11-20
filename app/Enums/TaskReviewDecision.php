<?php

namespace App\Enums;

enum TaskReviewDecision: string
{
    case RevisionRequested = 'revision_requested';
    case Accepted = 'accepted';

    public function label(): string
    {
        return match ($this) {
            self::RevisionRequested => 'Perlu Revisi',
            self::Accepted => 'Tugas Diterima (Accepted)',
        };
    }

    public function toTaskStatus(): TaskStatus
    {
        return match ($this) {
            self::RevisionRequested => TaskStatus::RevisionRequested,
            self::Accepted => TaskStatus::Accepted,
        };
    }
}
