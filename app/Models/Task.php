<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable([
    'pic_user_id',
    'intern_user_id',
    'title',
    'description',
    'assigned_at',
    'deadline_at',
    'status',
    'accepted_at',
    'accepted_by',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public const TIMEZONE = 'Asia/Jakarta';

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'deadline_at' => 'datetime',
            'status' => TaskStatus::class,
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * PIC yang membuat / menugaskan tugas.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    /**
     * Peserta magang penerima tugas.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_user_id');
    }

    /**
     * Pengguna (PIC) yang menerima / menyetujui tugas selesai.
     */
    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /**
     * Catatan logbook otomatis yang terbentuk saat tugas ini diberikan.
     */
    public function assignmentLogbook(): HasOne
    {
        return $this->hasOne(Logbook::class, 'task_id');
    }

    /**
     * Alias relasi logbook otomatis untuk konsistensi pemanggilan Task::logbook().
     */
    public function logbook(): HasOne
    {
        return $this->hasOne(Logbook::class, 'task_id');
    }

    /**
     * Menentukan apakah tugas sudah melewati deadline terbaru (WIB) dan belum berstatus accepted.
     */
    public function isOverdue(?Carbon $referenceTime = null): bool
    {
        if (! $this->deadline_at) {
            return false;
        }

        if ($this->status === TaskStatus::Accepted) {
            return false;
        }

        $now = $referenceTime ?? Carbon::now(self::TIMEZONE);

        return $this->deadline_at->lt($now);
    }

    public function canBeEditedByPic(): bool
    {
        return $this->status?->canBeEditedByPic() ?? false;
    }

    public function canBeStartedByIntern(): bool
    {
        return $this->status?->canBeStartedByIntern() ?? false;
    }

    /**
     * Scope tugas yang sudah melewati deadline terbaru (WIB) dan belum accepted (untuk indikator EWS Modul 4).
     */
    public function scopeOverdue(Builder $query, ?Carbon $referenceTime = null): Builder
    {
        $now = ($referenceTime ?? Carbon::now(self::TIMEZONE))->toDateTimeString();

        return $query
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->where('status', '!=', TaskStatus::Accepted->value);
    }

    /**
     * Scope visibilitas tugas sesuai role pengguna:
     * - Admin: seluruh tugas
     * - PIC: tugas yang dibuat oleh PIC tersebut
     * - Intern: tugas yang ditugaskan kepada peserta tersebut
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isPic()) {
            return $query->where('pic_user_id', $user->id);
        }

        return $query->where('intern_user_id', $user->id);
    }
}
