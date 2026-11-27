<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EwsSetting extends Model
{
    use HasFactory;

    protected $table = 'ews_settings';

    protected $fillable = [
        'attendance_cutoff_time',
        'logbook_cutoff_time',
        'updated_by',
    ];

    /**
     * Pengguna (Admin/HR) yang terakhir kali memperbarui pengaturan cutoff.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Singleton / baris pengaturan aktif dengan fallback aman.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'attendance_cutoff_time' => '09:00',
                'logbook_cutoff_time' => '17:00',
                'updated_by' => null,
            ]
        );
    }
}
