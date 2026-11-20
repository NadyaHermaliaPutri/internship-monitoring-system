<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit sesuai Database Dictionary v1.0 (singular).
     */
    protected $table = 'attendance';

    protected $fillable = [
        'intern_user_id',
        'attendance_date',
        'checked_in_at',
        'status',
        'verification_status',
        'photo_media_id',
        'reviewed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Peserta magang pemilik data presensi.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_user_id');
    }

    /**
     * Foto bukti presensi selfie di tabel media_files.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'photo_media_id');
    }

    /**
     * Admin/HR yang meninjau atau mengoreksi presensi.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isOnTime(): bool
    {
        return $this->status === 'on_time';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isReviewed(): bool
    {
        return ! is_null($this->reviewed_by);
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    public function isSubmitted(): bool
    {
        return $this->verification_status === 'submitted';
    }

    public function isValidPresence(): bool
    {
        return $this->verification_status !== 'rejected';
    }

    /**
     * Scope filter status tepat waktu.
     */
    public function scopeOnTime(Builder $query): Builder
    {
        return $query->where('status', 'on_time');
    }

    /**
     * Scope filter status terlambat.
     */
    public function scopeLate(Builder $query): Builder
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope filter tanggal tertentu.
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('attendance_date', $date);
    }

    /**
     * Scope filter presensi sah (tidak ditolak oleh HR).
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('verification_status', '!=', 'rejected');
    }

    /**
     * Scope filter presensi yang tidak ditolak.
     */
    public function scopeNotRejected(Builder $query): Builder
    {
        return $query->where('verification_status', '!=', 'rejected');
    }

    /**
     * Scope filter presensi yang ditolak oleh HR.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('verification_status', 'rejected');
    }

    /**
     * Scope filter presensi berstatus submitted.
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('verification_status', 'submitted');
    }

    /**
     * Scope filter rentang tanggal.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }
}
