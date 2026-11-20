<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tentukan URL redirect dashboard berdasarkan role.
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => '/admin/dashboard',
            'pic' => '/pic/dashboard',
            'intern' => '/intern/dashboard',
            default => '/',
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPic(): bool
    {
        return $this->role === 'pic';
    }

    public function isIntern(): bool
    {
        return $this->role === 'intern';
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Eloquent Modul 2 (Presensi, Izin, Penugasan PIC, & File)
    |--------------------------------------------------------------------------
    */

    /**
     * Data presensi yang dimiliki peserta magang.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'intern_user_id');
    }

    /**
     * Data pengajuan izin/sakit yang diajukan peserta magang.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'intern_user_id');
    }

    /**
     * File privat yang diunggah pengguna.
     */
    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'owner_user_id');
    }

    /**
     * Penugasan PIC untuk peserta magang (jika role = intern).
     */
    public function internPicAssignments(): HasMany
    {
        return $this->hasMany(PicAssignment::class, 'intern_user_id');
    }

    /**
     * Penugasan peserta yang dibimbing oleh PIC (jika role = pic).
     */
    public function picAssignments(): HasMany
    {
        return $this->hasMany(PicAssignment::class, 'pic_user_id');
    }

    /**
     * Memeriksa apakah PIC ini aktif membimbing peserta tertentu.
     */
    public function isCurrentlyAssignedTo(int $internUserId): bool
    {
        if (! $this->isPic()) {
            return false;
        }

        return $this->picAssignments()
            ->active()
            ->where('intern_user_id', $internUserId)
            ->exists();
    }
    public function internProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InternProfile::class);
    }

    public function picProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PicProfile::class);
    }
}
