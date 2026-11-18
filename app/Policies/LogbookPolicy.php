<?php

namespace App\Policies;

use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\User;

class LogbookPolicy
{
    /**
     * Semua role aktif (intern, pic, admin) boleh mengakses halaman daftar/riwayat sesuai cakupannya.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['intern', 'pic', 'admin'], true);
    }

    /**
     * Melihat detail satu logbook:
     * - Admin: seluruh logbook
     * - Intern: hanya miliknya sendiri
     * - PIC: hanya logbook peserta yang berada dalam periode penugasannya di pic_assignments
     */
    public function view(User $user, Logbook $logbook): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isIntern()) {
            return $logbook->intern_user_id === $user->id;
        }

        if ($user->isPic()) {
            return Logbook::isPicAuthorizedForInternOnDate(
                $user->id,
                $logbook->intern_user_id,
                $logbook->log_date->toDateString()
            );
        }

        return false;
    }

    /**
     * Hanya peserta aktif yang dapat membuat logbook manual.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->isIntern();
    }

    /**
     * Peserta hanya dapat mengedit logbook miliknya sendiri yang:
     * - Bersumber dari input manual (bukan task_assignment otomatis)
     * - Berstatus pending atau revision_requested (bukan approved)
     */
    public function update(User $user, Logbook $logbook): bool
    {
        if (! $user->is_active || ! $user->isIntern()) {
            return false;
        }

        if ($logbook->intern_user_id !== $user->id) {
            return false;
        }

        return $logbook->canBeEditedByIntern();
    }

    /**
     * Review / Approval logbook:
     * - Hanya boleh dilakukan oleh PIC aktif yang berwenang atas peserta pada tanggal logbook tersebut
     * - Hanya untuk logbook manual dengan status pending (logbook otomatis tidak masuk alur approval)
     */
    public function review(User $user, Logbook $logbook): bool
    {
        if (! $user->is_active || ! $user->isPic()) {
            return false;
        }

        if (! $logbook->isManual() || $logbook->status !== LogbookStatus::Pending) {
            return false;
        }

        return Logbook::isPicAuthorizedForInternOnDate(
            $user->id,
            $logbook->intern_user_id,
            $logbook->log_date->toDateString()
        );
    }

    /**
     * Data historis logbook tidak boleh dihapus oleh siapa pun.
     */
    public function delete(User $user, Logbook $logbook): bool
    {
        return false;
    }
}
