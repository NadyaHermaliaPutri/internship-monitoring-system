<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function internProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InternProfile::class);
    }

    public function picProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PicProfile::class);
    }
}
