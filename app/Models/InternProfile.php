<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'intern_code',
        'institution',
        'study_program',
        'internship_start_date',
        'internship_end_date',
    ];

    protected function casts(): array
    {
        return [
            'internship_start_date' => 'date',
            'internship_end_date' => 'date',
        ];
    }

    /**
     * Relasi ke User pemilik akun login.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung durasi magang dalam bulan.
     */
    public function getDurationInMonthsAttribute(): int
    {
        if (! $this->internship_start_date) {
            return 0;
        }

        $endDate = $this->internship_end_date ?? Carbon::now();
        
        // Pembulatan bulan dari selisih hari
        $months = (int) round($this->internship_start_date->floatDiffInMonths($endDate));

        return max(1, $months);
    }

    /**
     * Teks periode magang yang terformat rapi.
     */
    public function getFormattedPeriodAttribute(): string
    {
        if ($this->internship_start_date && $this->internship_end_date) {
            return $this->internship_start_date->translatedFormat('d M Y') . ' – ' . $this->internship_end_date->translatedFormat('d M Y');
        }

        if ($this->internship_start_date) {
            return 'Mulai: ' . $this->internship_start_date->translatedFormat('d M Y');
        }

        return 'Belum diatur';
    }
}
