<?php

namespace App\Models;

use App\Enums\TaskReviewDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable([
    'task_submission_id',
    'reviewed_by',
    'decision',
    'feedback',
    'reviewed_at',
])]
class TaskReview extends Model
{
    protected function casts(): array
    {
        return [
            'decision' => TaskReviewDecision::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Guard Immutability (Append-Only):
     * Riwayat peninjauan PIC tidak pernah boleh di-update atau dihapus setelah tersimpan.
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Riwayat review tugas (TaskReview) bersifat append-only dan tidak boleh diubah.');
        });

        static::deleting(function (): void {
            throw new LogicException('Riwayat review tugas (TaskReview) bersifat historis dan tidak boleh dihapus.');
        });
    }

    /**
     * Pengumpulan (submission) tugas yang ditinjau.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(TaskSubmission::class, 'task_submission_id');
    }

    /**
     * PIC yang melakukan peninjauan / memberikan keputusan.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
