<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar presensi.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat presensi tertentu.
     * Aturan:
     * - Admin/HR: dapat melihat presensi semua peserta.
     * - Peserta: hanya dapat melihat presensi miliknya sendiri.
     * - PIC: hanya dapat melihat presensi peserta yang aktif ditugaskan kepadanya di pic_assignments.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isIntern()) {
            return $user->id === $attendance->intern_user_id;
        }

        if ($user->isPic()) {
            return $user->isCurrentlyAssignedTo($attendance->intern_user_id);
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat melakukan check-in presensi (hanya intern).
     */
    public function create(User $user): bool
    {
        return $user->isIntern();
    }

    /**
     * Menentukan apakah pengguna dapat mengoreksi / meninjau presensi (hanya Admin/HR).
     * PIC dan intern DILARANG KERAS mengoreksi presensi.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menolak presensi (hanya Admin/HR).
     */
    public function reject(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus presensi (hanya Admin/HR).
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin();
    }
}
