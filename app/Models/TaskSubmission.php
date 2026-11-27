<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

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
     * Guard Immutability (Append-Only):
     * Riwayat pengumpulan tugas tidak pernah boleh di-update atau dihapus setelah tersimpan.
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Riwayat pengumpulan tugas (TaskSubmission) bersifat append-only dan tidak boleh diubah.');
        });

        static::deleting(function (): void {
            throw new LogicException('Riwayat pengumpulan tugas (TaskSubmission) bersifat historis dan tidak boleh dihapus.');
        });
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

    /**
     * Seluruh riwayat review PIC untuk submission ini.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(TaskReview::class, 'task_submission_id')->orderByDesc('reviewed_at')->orderByDesc('id');
    }

    /**
     * Review PIC terbaru pada submission ini.
     */
    public function latestReview(): HasOne
    {
        return $this->hasOne(TaskReview::class, 'task_submission_id')->latestOfMany();
    }
}
