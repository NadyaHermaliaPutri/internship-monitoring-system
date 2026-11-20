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
     * Tugas terkait apabila logbook ini berasal dari penugasan otomatis PIC.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
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
     * Scope visibilitas logbook untuk PIC berdasarkan tabel pic_assignments
     * dengan aturan periode: start_date <= log_date DAN (end_date IS NULL OR end_date >= log_date).
     */
    public function scopeForPic(Builder $query, int $picUserId): Builder
    {
        return $query->whereExists(function ($sub) use ($picUserId) {
            $sub->select(DB::raw(1))
                ->from('pic_assignments')
                ->whereColumn('pic_assignments.intern_user_id', 'logbooks.intern_user_id')
                ->where('pic_assignments.pic_user_id', $picUserId)
                ->whereColumn('pic_assignments.start_date', '<=', 'logbooks.log_date')
                ->where(function ($q) {
                    $q->whereNull('pic_assignments.end_date')
                        ->orWhereColumn('pic_assignments.end_date', '>=', 'logbooks.log_date');
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
     * Titik terpusat untuk memeriksa apakah seorang PIC berwenang atas peserta pada tanggal tertentu.
     */
    public static function isPicAuthorizedForInternOnDate(int $picUserId, int $internUserId, string $logDate): bool
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
}
