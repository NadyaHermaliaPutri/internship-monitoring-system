<?php

namespace App\Enums;

enum LogbookSource: string
{
    case Manual = 'manual';
    case TaskAssignment = 'task_assignment';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Input Manual',
            self::TaskAssignment => 'Penugasan PIC (Otomatis)',
        };
    }

    public function isManual(): bool
    {
        return $this === self::Manual;
    }

    public function isTaskAssignment(): bool
    {
        return $this === self::TaskAssignment;
    }
}
