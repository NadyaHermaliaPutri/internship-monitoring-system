<?php

namespace App\Models;

use App\Enums\LogbookActivityStatus;
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
    'evidence_media_id',
    'activity_status',
    'source',
    'task_id',
    'related_task_id',
    'target_pic_user_id',
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
            'activity_status' => LogbookActivityStatus::class,
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
     * Bukti gambar / screenshot hasil kegiatan yang disimpan di storage privat.
     */
    public function evidenceMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'evidence_media_id');
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

    /**
     * PIC tujuan / pemberi tugas yang dipilih peserta saat mengisi logbook manual (nullable = Mandiri / Umum).
     */
    public function targetPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_pic_user_id');
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
        return $this->isManual() && ($this->status === null || $this->status->isEditableByIntern());
    }

    public function canBeCompletedByIntern(): bool
    {
        return $this->isManual()
            && ($this->status === null || $this->status->isEditableByIntern())
            && $this->activity_status === LogbookActivityStatus::SedangDikerjakan;
    }

    public function canBeReviewedByPic(): bool
    {
        return $this->isManual()
            && $this->target_pic_user_id !== null
            && $this->activity_status === LogbookActivityStatus::Selesai
            && $this->status === LogbookStatus::Pending;
    }

    /**
     * Mengembalikan 1 badge status utama yang bersih dan tidak bertumpuk untuk kolom STATUS.
     *
     * @return array{label: string, classes: string, title: ?string}
     */
    public function displayStatusBadge(): array
    {
        if ($this->isManual()) {
            if ($this->status === LogbookStatus::Approved) {
                return [
                    'label' => 'Disetujui',
                    'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'title' => null,
                ];
            }

            if ($this->status === LogbookStatus::RevisionRequested) {
                return [
                    'label' => 'Perlu Revisi',
                    'classes' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'title' => null,
                ];
            }

            if (
                $this->status === LogbookStatus::Pending
                && $this->target_pic_user_id !== null
                && $this->activity_status === LogbookActivityStatus::Selesai
            ) {
                return [
                    'label' => 'Menunggu Review',
                    'classes' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'title' => null,
                ];
            }

            if ($this->activity_status === LogbookActivityStatus::Selesai) {
                return [
                    'label' => 'Selesai',
                    'classes' => 'bg-teal-50 text-teal-700 border-teal-200',
                    'title' => null,
                ];
            }

            return [
                'label' => 'Sedang Dikerjakan',
                'classes' => 'bg-sky-50 text-sky-700 border-sky-200',
                'title' => null,
            ];
        }

        if ($this->isTaskAssignment() && $this->task?->status) {
            $taskStatusClasses = match ($this->task->status->value) {
                'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };

            return [
                'label' => $this->task->status->label(),
                'classes' => $taskStatusClasses,
                'title' => 'Status real-time dari menu Tugas Peserta (read-only)',
            ];
        }

        return [
            'label' => 'Tercatat Otomatis',
            'classes' => 'bg-sky-50/70 text-sky-700 border-sky-200/70',
            'title' => null,
        ];
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
     * Scope visibilitas logbook untuk PIC:
     * 1) source = task_assignment -> hanya dilihat oleh PIC pembuat tugas pada task_id tersebut.
     * 2) source = manual -> HANYA logbook yang ditujukan langsung ke PIC ini (target_pic_user_id = $picUserId).
     *    Logbook Mandiri / Umum (target_pic_user_id IS NULL) tidak tampil di halaman PIC mana pun (hanya Peserta & Admin).
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
            // 2. Logbook manual yang ditujukan khusus ke PIC ini
                ->orWhere(function (Builder $q) use ($picUserId) {
                    $q->where('logbooks.source', LogbookSource::Manual->value)
                        ->where('logbooks.target_pic_user_id', $picUserId);
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

                return $taskPicId !== null && (int) $taskPicId === $picUserId;
            }

            return false;
        }

        // Logbook manual hanya boleh dilihat oleh PIC yang dipilih (target_pic_user_id); Mandiri (null) ditolak untuk PIC
        return $this->target_pic_user_id !== null && (int) $this->target_pic_user_id === $picUserId;
    }

    /**
     * Memeriksa apakah PIC diizinkan ME-REVIEW (Approve / Minta Revisi) logbook ini.
     */
    public function isReviewableByPic(int $picUserId): bool
    {
        if (! $this->canBeReviewedByPic()) {
            return false;
        }

        return (int) $this->target_pic_user_id === $picUserId;
    }
}
