<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'evidence_media_id',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Peserta magang pemohon izin/sakit.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_user_id');
    }

    /**
     * Berkas bukti pendukung (surat dokter / dokumen).
     */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'evidence_media_id');
    }

    /**
     * Admin/HR yang meninjau dan memberi keputusan.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSick(): bool
    {
        return $this->leave_type === 'sick';
    }

    public function isLeave(): bool
    {
        return $this->leave_type === 'leave';
    }

    /**
     * Menghitung durasi hari kalender dari pengajuan.
     */
    public function getDurationDaysAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 1;
        }

        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Memeriksa apakah peserta memiliki izin/sakit yang sudah disetujui pada tanggal tertentu.
     */
    public function scopeActiveOnDate(Builder $query, string $date): Builder
    {
        return $query->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }
}
