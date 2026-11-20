<?php

namespace App\Policies;

use App\Models\InternProfile;
use App\Models\User;

/**
 * InternProfilePolicy — Otorisasi akses ke data profil peserta magang.
 *
 * Aturan Bisnis PT WAN Teknologi Internasional:
 *   - Admin / HR   : Akses penuh ke seluruh profil peserta.
 *   - PIC          : Hanya dapat melihat profil peserta yang ditugaskan kepadanya
 *                    (melalui tabel pic_assignments jika tersedia; fallback ke akses
 *                    baca saja selama relasi assignment belum diimplementasikan).
 *   - Intern       : Hanya dapat mengakses data profilnya sendiri.
 */
class InternProfilePolicy
{
    /**
     * Admin melewati semua pengecekan Policy secara otomatis.
     * Method ini dijalankan sebelum method lainnya.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true; // Admin selalu diizinkan tanpa cek lebih lanjut
        }

        return null; // Lanjutkan ke method Policy spesifik untuk role lain
    }

    /**
     * Apakah user boleh melihat daftar semua profil peserta?
     * Hanya Admin (sudah ditangani `before`) dan PIC yang diizinkan.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPic();
    }

    /**
     * Apakah user boleh melihat detail satu profil peserta tertentu?
     *
     * - PIC   : Hanya jika peserta tersebut ditugaskan kepadanya.
     * - Intern: Hanya profil miliknya sendiri.
     */
    public function view(User $user, InternProfile $internProfile): bool
    {
        // Intern hanya boleh melihat profilnya sendiri
        if ($user->isIntern()) {
            return $user->id === $internProfile->user_id;
        }

        // PIC: periksa relasi assignment
        if ($user->isPic()) {
            return $this->picIsAssignedToIntern($user, $internProfile);
        }

        return false;
    }

    /**
     * Apakah user boleh membuat profil baru?
     * Hanya Admin (sudah ditangani `before`).
     */
    public function create(User $user): bool
    {
        return false; // Non-admin tidak diizinkan membuat profil peserta lain
    }

    /**
     * Apakah user boleh memperbarui profil peserta tertentu?
     *
     * - Intern: Hanya dapat memperbarui profilnya sendiri.
     * - PIC   : Tidak diizinkan mengubah profil (hanya bisa melihat).
     */
    public function update(User $user, InternProfile $internProfile): bool
    {
        if ($user->isIntern()) {
            return $user->id === $internProfile->user_id;
        }

        return false; // PIC tidak memiliki hak edit profil peserta
    }

    /**
     * Apakah user boleh menghapus profil? Tidak ada yang boleh kecuali Admin.
     */
    public function delete(User $user, InternProfile $internProfile): bool
    {
        return false; // Hanya Admin (sudah ditangani `before`)
    }

    // -------------------------------------------------------------------------
    // Helper: Periksa Apakah PIC Ditugaskan ke Peserta Ini
    // -------------------------------------------------------------------------

    /**
     * Memeriksa apakah PIC memiliki relasi penugasan yang sah terhadap peserta.
     *
     * Implementasi saat ini menggunakan kolom `pic_user_id` pada tabel intern_profiles
     * jika tersedia. Jika tabel pic_assignments terpisah diimplementasikan oleh modul
     * lain, ganti logika di sini tanpa mengubah interface Policy.
     *
     * @param  User          $pic           Akun PIC yang sedang login.
     * @param  InternProfile $internProfile  Profil peserta yang ingin diakses.
     */
    private function picIsAssignedToIntern(User $pic, InternProfile $internProfile): bool
    {
        // Opsi 1: Kolom pic_user_id ada di tabel intern_profiles
        if (isset($internProfile->pic_user_id)) {
            return $pic->id === $internProfile->pic_user_id;
        }

        // Opsi 2: Relasi melalui tabel pivot pic_assignments (siap diperluas)
        // Uncomment baris berikut jika tabel pic_assignments sudah dibuat:
        //
        // return \DB::table('pic_assignments')
        //     ->where('pic_user_id', $pic->id)
        //     ->where('intern_user_id', $internProfile->user_id)
        //     ->exists();

        // Fallback sementara: PIC dapat melihat semua peserta (read-only)
        // sampai tabel assignment diimplementasikan oleh modul terkait.
        return true;
    }
}
