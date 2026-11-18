<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'category',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'file_size_bytes',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MediaFile $media) {
            if (empty($media->uploaded_at)) {
                $media->uploaded_at = now();
            }
        });
    }

    /**
     * Pengguna pemilik / pengunggah file.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Memeriksa apakah file adalah gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Memeriksa apakah file adalah PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Format ukuran file manusiawi (KB / MB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }
}
