<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarException extends Model
{
    use HasFactory;

    protected $fillable = [
        'exception_date',
        'type',
        'name',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exception_date' => 'date',
        ];
    }

    /**
     * Admin/HR yang membuat pengecualian kalender.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk hari libur.
     */
    public function scopeHoliday(Builder $query): Builder
    {
        return $query->where('type', 'holiday');
    }

    /**
     * Scope untuk hari kerja khusus.
     */
    public function scopeWorkingDay(Builder $query): Builder
    {
        return $query->where('type', 'working_day');
    }

    /**
     * Scope rentang tanggal tertentu.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereDate('exception_date', '>=', $startDate)
            ->whereDate('exception_date', '<=', $endDate);
    }

    public function isHoliday(): bool
    {
        return $this->type === 'holiday';
    }

    public function isWorkingDay(): bool
    {
        return $this->type === 'working_day';
    }
}
