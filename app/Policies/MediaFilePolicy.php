<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;

class MediaFilePolicy
{
    /**
     * Menentukan apakah pengguna diizinkan mengakses/melihat file privat tertentu.
     * Sesuai aturan:
     * - Admin/HR: memiliki akses ke seluruh file bukti privat.
     * - Pemilik file: dapat mengakses berkas miliknya sendiri.
     * - PIC & peserta lain: dilarang mengakses kategori attendance_photo dan leave_evidence (403 Forbidden).
     */
    public function view(User $user, MediaFile $mediaFile): bool
    {
        // 1. Admin/HR memiliki akses penuh
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Pemilik file dapat mengakses miliknya
        if ($user->id === $mediaFile->owner_user_id) {
            return true;
        }

        // 3. Kategori attendance_photo dan leave_evidence hanya untuk Admin dan pemilik file
        if (in_array($mediaFile->category, ['attendance_photo', 'leave_evidence'], true)) {
            return false;
        }

        return false;
    }

    /**
     * Pengunggahan file ditangani oleh controller/service masing-masing entitas.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Penghapusan file hanya oleh Admin atau pemilik.
     */
    public function delete(User $user, MediaFile $mediaFile): bool
    {
        return $user->isAdmin() || $user->id === $mediaFile->owner_user_id;
    }
}
