<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

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
     * Seluruh riwayat pengumpulan tugas (diurutkan dari versi terbaru ke terlama).
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class, 'task_id')->orderByDesc('submission_number');
    }

    /**
     * Pengumpulan (submission) versi terakhir.
     */
    public function latestSubmission(): HasOne
    {
        return $this->hasOne(TaskSubmission::class, 'task_id')->latestOfMany('submission_number');
    }

    /**
     * Seluruh riwayat review PIC untuk tugas ini melalui task_submissions.
     */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            TaskReview::class,
            TaskSubmission::class,
            'task_id',
            'task_submission_id',
            'id',
            'id'
        )->orderByDesc('task_reviews.reviewed_at')->orderByDesc('task_reviews.id');
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

    public function canBeSubmittedByIntern(): bool
    {
        return $this->status?->canBeSubmittedByIntern() ?? false;
    }

    public function canBeReviewedByPic(): bool
    {
        return $this->status?->canBeReviewedByPic() ?? false;
    }

    /**
     * Memeriksa apakah sebuah transisi status sah bagi aktor tertentu berdasarkan:
     * 1) Status aktif aktor
     * 2) Kepemilikan resource (intern_user_id untuk peserta, pic_user_id untuk PIC)
     * 3) Matriks state machine TaskStatus::canTransitionTo()
     */
    public function canTransitionTo(TaskStatus $target, User $actor): bool
    {
        if (! $actor->is_active || ! $this->status) {
            return false;
        }

        if ($actor->isIntern() && $this->intern_user_id !== $actor->id) {
            return false;
        }

        if ($actor->isPic() && $this->pic_user_id !== $actor->id) {
            return false;
        }

        if ($actor->isAdmin()) {
            return false;
        }

        return $this->status->canTransitionTo($target, $actor->role);
    }

    /**
     * Menegakkan validasi state machine terpusat; melempar ValidationException (422)
     * apabila transisi status tidak diizinkan bagi aktor atau dari status saat ini.
     */
    public function assertCanTransitionTo(TaskStatus $target, User $actor): void
    {
        if (! $actor->is_active) {
            throw ValidationException::withMessages([
                'task' => 'Akun tidak aktif tidak dapat mengubah status tugas.',
            ]);
        }

        if (($actor->isIntern() && $this->intern_user_id !== $actor->id)
            || ($actor->isPic() && $this->pic_user_id !== $actor->id)
            || $actor->isAdmin()
        ) {
            throw ValidationException::withMessages([
                'task' => 'Anda tidak memiliki wewenang atas tugas ini.',
            ]);
        }

        if (! $this->status || ! $this->status->canTransitionTo($target, $actor->role)) {
            $currentLabel = $this->status?->label() ?? 'Tidak Diketahui';
            $targetLabel = $target->label();

            throw ValidationException::withMessages([
                'status' => "Transisi status tugas dari '{$currentLabel}' ke '{$targetLabel}' tidak diizinkan.",
            ]);
        }
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
