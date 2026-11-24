<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Menyediakan 4 akun demo minimal yang mencakup seluruh role
     * dan skenario status akun untuk keperluan pengujian sistem.
     * Aman dijalankan berulang kali (idempotent via updateOrCreate).
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        // ----------------------------------------------------------------
        // 1. ADMIN / HR — Pengelola sistem, tidak memiliki profil tambahan
        // ----------------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'admin@ptwan.com'],
            [
                'name'              => 'Admin HR PT WAN',
                'password'          => $password,
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ----------------------------------------------------------------
        // 2. PIC / PEMBIMBING — Memiliki relasi picProfile (departemen)
        // ----------------------------------------------------------------
        $pic = User::updateOrCreate(
            ['email' => 'pic@ptwan.com'],
            [
                'name'              => 'Kak Nazwa',
                'password'          => $password,
                'role'              => 'pic',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $pic->picProfile()->updateOrCreate(
            ['user_id' => $pic->id],
            [
                'employee_code' => 'PIC-001',
                'department'    => 'Divisi Web Development',
            ]
        );

        // ----------------------------------------------------------------
        // 3. INTERN AKTIF — Memiliki relasi internProfile, masa magang berjalan
        // ----------------------------------------------------------------
        $internAktif = User::updateOrCreate(
            ['email' => 'intern@ptwan.com'],
            [
                'name'              => 'Ahmad Fikri',
                'password'          => $password,
                'role'              => 'intern',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $internAktif->internProfile()->updateOrCreate(
            ['user_id' => $internAktif->id],
            [
                'institution'           => 'Politeknik Negeri Padang',
                'study_program'         => 'Teknologi Informasi',
                'internship_start_date' => '2026-09-01',
                'internship_end_date'   => '2026-11-30',
            ]
        );

        // ----------------------------------------------------------------
        // 4. INTERN NONAKTIF / ALUMNI — is_active false, masa magang selesai
        // ----------------------------------------------------------------
        $internAlumni = User::updateOrCreate(
            ['email' => 'intern.nonaktif@ptwan.com'],
            [
                'name'              => 'Siti Nurhaliza',
                'password'          => $password,
                'role'              => 'intern',
                'is_active'         => false,
                'email_verified_at' => now(),
            ]
        );

        $internAlumni->internProfile()->updateOrCreate(
            ['user_id' => $internAlumni->id],
            [
                'institution'           => 'Universitas Andalas',
                'study_program'         => 'Sistem Informasi',
                'internship_start_date' => '2026-06-01',
                'internship_end_date'   => '2026-08-31',
            ]
        );
    }
}
