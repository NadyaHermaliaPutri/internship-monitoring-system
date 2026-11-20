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

    public function canBeStartedByIntern(): bool
    {
        return $this === self::Assigned;
    }

    /**
     * Peserta dapat mengumpulkan tugas saat status assigned, in_progress, atau revision_requested.
     */
    public function canBeSubmittedByIntern(): bool
    {
        return in_array($this, [
            self::Assigned,
            self::InProgress,
            self::RevisionRequested,
        ], true);
    }

    /**
     * Menentukan status tugas baru setelah peserta mengumpulkan bukti.
     */
    public function nextStatusAfterSubmission(): self
    {
        return match ($this) {
            self::RevisionRequested => self::Resubmitted,
            default => self::Submitted,
        };
    }
}
