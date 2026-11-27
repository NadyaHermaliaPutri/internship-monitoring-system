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

    /**
     * PIC hanya dapat meninjau tugas apabila status saat ini adalah submitted atau resubmitted.
     */
    public function canBeReviewedByPic(): bool
    {
        return in_array($this, [
            self::Submitted,
            self::Resubmitted,
        ], true);
    }

    /**
     * Daftar status berikutnya yang diizinkan dari status saat ini berdasarkan role aktor.
     *
     * @return array<int, self>
     */
    public function allowedNextStatuses(?string $actorRole = null): array
    {
        return match ($this) {
            self::Assigned => match ($actorRole) {
                'intern' => [self::InProgress, self::Submitted],
                'pic' => [],
                'admin' => [],
                null => [self::InProgress, self::Submitted],
                default => [],
            },
            self::InProgress => match ($actorRole) {
                'intern' => [self::Submitted],
                'pic' => [],
                'admin' => [],
                null => [self::Submitted],
                default => [],
            },
            self::Submitted, self::Resubmitted => match ($actorRole) {
                'pic' => [self::RevisionRequested, self::Accepted],
                'intern' => [],
                'admin' => [],
                null => [self::RevisionRequested, self::Accepted],
                default => [],
            },
            self::RevisionRequested => match ($actorRole) {
                'intern' => [self::Resubmitted],
                'pic' => [],
                'admin' => [],
                null => [self::Resubmitted],
                default => [],
            },
            self::Accepted => [],
        };
    }

    /**
     * Memeriksa apakah transisi menuju $target sah menurut matriks state machine dan role aktor.
     */
    public function canTransitionTo(self $target, ?string $actorRole = null): bool
    {
        return in_array($target, $this->allowedNextStatuses($actorRole), true);
    }
}
