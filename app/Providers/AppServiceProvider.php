<?php

namespace App\Providers;

use App\Models\InternProfile;
use App\Models\User;
use App\Policies\InternProfilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Mendaftarkan seluruh Laravel Policy sesuai aturan otorisasi
     * PT WAN Teknologi Internasional (Tugas Anggota 1 — Nomor 6).
     */
    public function boot(): void
    {
        // Policy: Akses ke data profil peserta magang (tabel intern_profiles)
        Gate::policy(InternProfile::class, InternProfilePolicy::class);

        // Policy: Akses ke data akun pengguna (tabel users)
        Gate::policy(User::class, UserPolicy::class);
    }
}
