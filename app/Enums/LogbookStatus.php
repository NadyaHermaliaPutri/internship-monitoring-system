<?php

namespace App\Enums;

enum LogbookStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case RevisionRequested = 'revision_requested';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::RevisionRequested => 'Perlu Revisi',
        };
    }

    public function isEditableByIntern(): bool
    {
        return in_array($this, [self::Pending, self::RevisionRequested], true);
    }
}
