<?php

namespace App\Models;

use App\Enums\TaskReviewDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
