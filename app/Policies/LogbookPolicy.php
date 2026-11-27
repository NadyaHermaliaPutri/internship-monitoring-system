<?php

namespace App\Policies;

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
     * - PIC:
     *   * source = task_assignment -> hanya PIC pembuat tugas pada task_id
     *   * source = manual & related_task_id terisi -> hanya PIC pembuat tugas pada related_task_id
     *   * source = manual & related_task_id = null (mandiri) -> semua PIC yang terkait dengan peserta (pic_assignments aktif / pernah memberi tugas)
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
            return $logbook->isViewableByPic($user->id);
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
     * Peserta hanya dapat menandai progres kegiatan mandiri miliknya menjadi "Selesai"
     * selama bersumber manual, belum approved (pending/revision_requested), dan progresnya masih "sedang_dikerjakan".
     */
    public function complete(User $user, Logbook $logbook): bool
    {
        if (! $user->is_active || ! $user->isIntern()) {
            return false;
        }

        if ($logbook->intern_user_id !== $user->id) {
            return false;
        }

        return $logbook->canBeCompletedByIntern();
    }

    /**
     * Review / Approval logbook:
     * - Hanya untuk logbook manual berstatus pending
     * - Jika related_task_id terisi: hanya PIC pembuat tugas tersebut
     * - Jika related_task_id = null (mandiri): hanya PIC pembimbing utama di pic_assignments aktif pada log_date
     */
    public function review(User $user, Logbook $logbook): bool
    {
        if (! $user->is_active || ! $user->isPic()) {
            return false;
        }

        return $logbook->isReviewableByPic($user->id);
    }

    /**
     * Data historis logbook tidak boleh dihapus oleh siapa pun.
     */
    public function delete(User $user, Logbook $logbook): bool
    {
        return false;
    }
}
