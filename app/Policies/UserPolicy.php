<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy — Otorisasi akses ke data akun pengguna (tabel users).
 *
 * Aturan Bisnis PT WAN Teknologi Internasional:
 *   - Admin / HR   : Akses penuh (CRUD) ke seluruh data akun.
 *   - PIC          : Hanya dapat melihat daftar akun intern yang ditugaskan.
 *   - Intern       : Hanya dapat melihat dan memperbarui data akunnya sendiri.
 */
class UserPolicy
{
    /**
     * Admin melewati semua pengecekan Policy secara otomatis.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Apakah user boleh melihat daftar semua akun?
     * Hanya Admin (via `before`) yang berhak atas daftar lengkap.
     */
    public function viewAny(User $user): bool
    {
        return false; // Non-admin tidak boleh melihat semua akun
    }

    /**
     * Apakah user boleh melihat detail akun tertentu?
     *
     * - PIC   : Dapat melihat akun intern yang ditugaskan padanya.
     * - Intern: Hanya akunnya sendiri.
     */
    public function view(User $user, User $model): bool
    {
        // Intern hanya boleh melihat akunnya sendiri
        if ($user->isIntern()) {
            return $user->id === $model->id;
        }

        // PIC dapat melihat akun dengan role intern
        // (pembatasan per-assignment dapat ditambahkan di sini)
        if ($user->isPic()) {
            return $model->isIntern();
        }

        return false;
    }

    /**
     * Apakah user boleh membuat akun baru?
     * Hanya Admin (via `before`).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Apakah user boleh memperbarui akun tertentu?
     *
     * - Intern: Hanya akunnya sendiri.
     * - PIC   : Tidak diizinkan mengubah akun siapapun melalui panel ini.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isIntern()) {
            return $user->id === $model->id;
        }

        return false;
    }

    /**
     * Apakah user boleh mengaktifkan/menonaktifkan akun (toggle status)?
     * Hanya Admin (via `before`), dan tidak dapat menyentuh akunnya sendiri
     * (guard tambahan ada di UserController::toggleStatus).
     */
    public function toggleStatus(User $user, User $model): bool
    {
        return false; // Non-admin tidak boleh toggle status siapapun
    }

    /**
     * Penghapusan akun dilarang keras sesuai kebijakan integritas data PT WAN.
     * Gunakan is_active = false sebagai gantinya.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }
}
