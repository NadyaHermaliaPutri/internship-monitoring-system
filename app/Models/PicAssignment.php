<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PicAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_user_id',
        'pic_user_id',
        'start_date',
        'end_date',
        'assigned_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Akun peserta magang yang ditugaskan.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_user_id');
    }

    /**
     * Akun PIC pembimbing.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    /**
     * Akun Admin/HR yang menetapkan penugasan.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Memeriksa apakah penugasan ini masih aktif (end_date IS NULL).
     */
    public function isActive(): bool
    {
        return is_null($this->end_date);
    }

    /**
     * Scope untuk penugasan yang masih aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('end_date');
    }

    /**
     * Scope untuk memfilter penugasan berdasarkan PIC.
     */
    public function scopeForPic(Builder $query, int $picUserId): Builder
    {
        return $query->where('pic_user_id', $picUserId);
    }

    /**
     * Scope untuk memfilter penugasan berdasarkan peserta.
     */
    public function scopeForIntern(Builder $query, int $internUserId): Builder
    {
        return $query->where('intern_user_id', $internUserId);
    }
}
