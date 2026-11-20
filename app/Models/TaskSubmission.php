<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id',
    'submitted_by',
    'submission_number',
    'submission_note',
    'submission_url',
    'evidence_media_id',
    'submitted_at',
])]
class TaskSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'submission_number' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Tugas terkait.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Peserta magang yang mengumpulkan bukti tugas.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Berkas bukti (video / dokumen / gambar) pada penyimpanan privat.
     */
    public function evidenceMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'evidence_media_id');
    }
}
