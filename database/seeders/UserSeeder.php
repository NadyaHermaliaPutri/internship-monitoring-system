<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat akun demo sintetis untuk 3 role dan pengujian status akun.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Akun Administrator / HR
        User::updateOrCreate(
            ['email' => 'admin@ptwan.com'],
            [
                'name' => 'Kak Fadly',
                'password' => $defaultPassword,
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun PIC (Person In Charge / Pembimbing)
        User::updateOrCreate(
            ['email' => 'pic@ptwan.com'],
            [
                'name' => 'Kak Nazwa',
                'password' => $defaultPassword,
                'role' => 'pic',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Akun Peserta Magang Aktif
        User::updateOrCreate(
            ['email' => 'intern@ptwan.com'],
            [
                'name' => 'Ahmad Fikri',
                'password' => $defaultPassword,
                'role' => 'intern',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 4. Akun Peserta Magang Nonaktif (Selesai Magang / Suspended)
        User::updateOrCreate(
            ['email' => 'intern.nonaktif@ptwan.com'],
            [
                'name' => 'Siti Nurhaliza (Alumni / Nonaktif)',
                'password' => $defaultPassword,
                'role' => 'intern',
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
