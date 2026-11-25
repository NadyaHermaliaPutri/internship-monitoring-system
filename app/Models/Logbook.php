<?php

namespace App\Models;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use Database\Factories\LogbookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'intern_user_id',
    'log_date',
    'title',
    'activity_description',
    'result',
    'source',
    'task_id',
    'related_task_id',
    'status',
    'reviewed_by',
    'reviewed_at',
    'review_note',
])]
class Logbook extends Model
{
    /** @use HasFactory<LogbookFactory> */
    use HasFactory;

    public const MAX_BACKDATE_DAYS = 7;

    public const TIMEZONE = 'Asia/Jakarta';

    protected function casts(): array
    {
        return [
            'log_date' => 'date:Y-m-d',
            'source' => LogbookSource::class,
            'status' => LogbookStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Peserta magang pemilik logbook.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_user_id');
    }

    /**
     * PIC yang meninjau logbook.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Riwayat revisi / perubahan logbook.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(LogbookRevision::class)->orderByDesc('revision_no');
    }

    /**
     * Tugas terkait apabila logbook ini berasal dari penugasan otomatis PIC (source = task_assignment).
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Tugas terkait apabila logbook manual peserta ditautkan ke suatu tugas PIC (source = manual).
     */
    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_task_id');
    }

    public function isManual(): bool
    {
        return $this->source === LogbookSource::Manual;
    }

    public function isTaskAssignment(): bool
    {
        return $this->source === LogbookSource::TaskAssignment;
    }

    public function canBeEditedByIntern(): bool
    {
        return $this->isManual() && ($this->status?->isEditableByIntern() ?? false);
    }

    public function canBeReviewedByPic(): bool
    {
        return $this->isManual() && $this->status === LogbookStatus::Pending;
    }

    /**
     * Scope hanya untuk logbook manual (dipakai untuk antrian approval & pemenuhan EWS).
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('source', LogbookSource::Manual->value);
    }

    /**
     * Scope hanya untuk logbook otomatis dari penugasan tugas.
     */
    public function scopeTaskAssignments(Builder $query): Builder
    {
        return $query->where('source', LogbookSource::TaskAssignment->value);
    }

    /**
     * Scope antrian approval: hanya manual dan status pending.
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->manual()->where('status', LogbookStatus::Pending->value);
    }

    /**
     * Scope pemenuhan logbook harian untuk EWS (hanya menghitung logbook manual).
     */
    public function scopeForEwsFulfillment(Builder $query, int $internUserId, string $date): Builder
    {
        return $query->manual()
            ->where('intern_user_id', $internUserId)
            ->whereDate('log_date', $date);
    }

    /**
     * Scope visibilitas logbook untuk PIC dengan dukungan Multi-PIC:
     * 1) source = task_assignment -> hanya dilihat oleh PIC pembuat tugas pada task_id tersebut.
     * 2) source = manual & related_task_id IS NOT NULL -> hanya dilihat oleh PIC pembuat tugas pada related_task_id tersebut.
     * 3) source = manual & related_task_id IS NULL (Kegiatan Mandiri) -> dilihat oleh PIC yang memiliki pic_assignments
     *    aktif pada log_date ATAU pernah memberi minimal 1 tugas di tabel tasks kepada peserta tersebut.
     */
    public function scopeForPic(Builder $query, int $picUserId): Builder
    {
        return $query->where(function (Builder $outer) use ($picUserId) {
            // 1. Logbook otomatis dari penugasan tugas milik PIC ini
            $outer->where(function (Builder $q) use ($picUserId) {
                $q->where('logbooks.source', LogbookSource::TaskAssignment->value)
                    ->whereNotNull('logbooks.task_id')
                    ->whereExists(function ($sub) use ($picUserId) {
                        $sub->select(DB::raw(1))
                            ->from('tasks')
                            ->whereColumn('tasks.id', 'logbooks.task_id')
                            ->where('tasks.pic_user_id', $picUserId);
                    });
            })
            // 2. Logbook manual yang terikat ke tugas milik PIC ini
                ->orWhere(function (Builder $q) use ($picUserId) {
                    $q->where('logbooks.source', LogbookSource::Manual->value)
                        ->whereNotNull('logbooks.related_task_id')
                        ->whereExists(function ($sub) use ($picUserId) {
                            $sub->select(DB::raw(1))
                                ->from('tasks')
                                ->whereColumn('tasks.id', 'logbooks.related_task_id')
                                ->where('tasks.pic_user_id', $picUserId);
                        });
                })
            // 3. Logbook manual mandiri (related_task_id IS NULL) milik peserta bimbingan/penugasan PIC ini
                ->orWhere(function (Builder $q) use ($picUserId) {
                    $q->where('logbooks.source', LogbookSource::Manual->value)
                        ->whereNull('logbooks.related_task_id')
                        ->where(function (Builder $rel) use ($picUserId) {
                            $rel->whereExists(function ($sub) use ($picUserId) {
                                $sub->select(DB::raw(1))
                                    ->from('pic_assignments')
                                    ->whereColumn('pic_assignments.intern_user_id', 'logbooks.intern_user_id')
                                    ->where('pic_assignments.pic_user_id', $picUserId)
                                    ->whereColumn('pic_assignments.start_date', '<=', 'logbooks.log_date')
                                    ->where(function ($dateQ) {
                                        $dateQ->whereNull('pic_assignments.end_date')
                                            ->orWhereColumn('pic_assignments.end_date', '>=', 'logbooks.log_date');
                                    });
                            })->orWhereExists(function ($sub) use ($picUserId) {
                                $sub->select(DB::raw(1))
                                    ->from('tasks')
                                    ->whereColumn('tasks.intern_user_id', 'logbooks.intern_user_id')
                                    ->where('tasks.pic_user_id', $picUserId);
                            });
                        });
                });
        });
    }

    /**
     * Scope visibilitas otomatis sesuai role pengguna.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isPic()) {
            return $query->forPic($user->id);
        }

        return $query->where('intern_user_id', $user->id);
    }

    /**
     * Memeriksa apakah PIC adalah PIC pembimbing utama peserta pada tanggal tertentu menurut tabel pic_assignments.
     */
    public static function isPicAssignedToInternOnDate(int $picUserId, int $internUserId, string $logDate): bool
    {
        return DB::table('pic_assignments')
            ->where('pic_user_id', $picUserId)
            ->where('intern_user_id', $internUserId)
            ->whereDate('start_date', '<=', $logDate)
            ->where(function ($q) use ($logDate) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $logDate);
            })
            ->exists();
    }

    /**
     * Alias kompatibilitas untuk pengecekan periode pic_assignments.
     */
    public static function isPicAuthorizedForInternOnDate(int $picUserId, int $internUserId, string $logDate): bool
    {
        return self::isPicAssignedToInternOnDate($picUserId, $internUserId, $logDate);
    }

    /**
     * Memeriksa apakah PIC terkait dengan peserta (melalui pic_assignments aktif pada log_date ATAU pernah memberi tugas di tasks).
     */
    public static function isPicRelatedToIntern(int $picUserId, int $internUserId, string $logDate): bool
    {
        if (self::isPicAssignedToInternOnDate($picUserId, $internUserId, $logDate)) {
            return true;
        }

        return DB::table('tasks')
            ->where('pic_user_id', $picUserId)
            ->where('intern_user_id', $internUserId)
            ->exists();
    }

    /**
     * Memeriksa apakah PIC diizinkan MELIHAT logbook ini.
     */
    public function isViewableByPic(int $picUserId): bool
    {
        if ($this->isTaskAssignment()) {
            if ($this->task_id) {
                $taskPicId = $this->relationLoaded('task') && $this->task
                    ? $this->task->pic_user_id
                    : DB::table('tasks')->where('id', $this->task_id)->value('pic_user_id');

                if ($taskPicId !== null) {
                    return (int) $taskPicId === $picUserId;
                }
            }

            return self::isPicAssignedToInternOnDate($picUserId, $this->intern_user_id, $this->log_date->toDateString());
        }

        // Logbook manual yang terikat ke suatu tugas PIC: hanya boleh dilihat oleh PIC pembuat tugas tersebut
        if ($this->related_task_id !== null) {
            $relatedPicId = $this->relationLoaded('relatedTask') && $this->relatedTask
                ? $this->relatedTask->pic_user_id
                : DB::table('tasks')->where('id', $this->related_task_id)->value('pic_user_id');

            return (int) $relatedPicId === $picUserId;
        }

        // Logbook manual mandiri (related_task_id = null): boleh dilihat oleh semua PIC yang terkait dengan peserta
        return self::isPicRelatedToIntern($picUserId, $this->intern_user_id, $this->log_date->toDateString());
    }

    /**
     * Memeriksa apakah PIC diizinkan ME-REVIEW (Approve / Minta Revisi) logbook ini.
     */
    public function isReviewableByPic(int $picUserId): bool
    {
        if (! $this->canBeReviewedByPic()) {
            return false;
        }

        // Jika terkait tugas tertentu: HANYA PIC pembuat tugas tersebut yang boleh melakukan approval/revisi
        if ($this->related_task_id !== null) {
            $relatedPicId = $this->relationLoaded('relatedTask') && $this->relatedTask
                ? $this->relatedTask->pic_user_id
                : DB::table('tasks')->where('id', $this->related_task_id)->value('pic_user_id');

            return (int) $relatedPicId === $picUserId;
        }

        // Jika kegiatan mandiri (related_task_id = null): hanya PIC pembimbing utama di pic_assignments aktif pada log_date
        return self::isPicAssignedToInternOnDate($picUserId, $this->intern_user_id, $this->log_date->toDateString());
    }
}
