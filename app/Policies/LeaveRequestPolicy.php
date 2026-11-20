<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar permohonan izin/sakit.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail pengajuan izin/sakit.
     * Aturan:
     * - Admin/HR: dapat melihat seluruh pengajuan izin.
     * - Peserta Magang: hanya dapat melihat pengajuan miliknya sendiri.
     * - PIC: hanya dapat melihat pengajuan peserta yang aktif ditugaskan kepadanya di pic_assignments.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isIntern()) {
            return $user->id === $leaveRequest->intern_user_id;
        }

        if ($user->isPic()) {
            return $user->isCurrentlyAssignedTo($leaveRequest->intern_user_id);
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat mengajukan permohonan izin/sakit (hanya intern).
     */
    public function create(User $user): bool
    {
        return $user->isIntern();
    }

    /**
     * Menentukan apakah pengguna berwenang menyetujui atau menolak permohonan izin (hanya Admin/HR).
     * LARANGAN KERAS: PIC dan intern dilarang keras memproses approval/rejection izin/sakit.
     */
    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat mengubah permohonan.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus permohonan.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isAdmin();
    }
}
