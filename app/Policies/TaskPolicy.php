<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Semua role aktif (intern, pic, admin) boleh mengakses daftar tugas sesuai cakupannya.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['intern', 'pic', 'admin'], true);
    }

    /**
     * Melihat detail tugas:
     * - Admin: seluruh tugas
     * - PIC: hanya tugas yang dibuat oleh PIC tersebut (pic_user_id === user->id)
     * - Intern: hanya tugas yang ditugaskan kepada dirinya (intern_user_id === user->id)
     */
    public function view(User $user, Task $task): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPic()) {
            return $task->pic_user_id === $user->id;
        }

        if ($user->isIntern()) {
            return $task->intern_user_id === $user->id;
        }

        return false;
    }

    /**
     * Hanya PIC aktif yang dapat membuat tugas baru.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->isPic();
    }

    /**
     * Hanya PIC pembuat tugas yang aktif yang boleh mengedit tugas/deadline,
     * dan hanya selama status tugas belum accepted.
     */
    public function update(User $user, Task $task): bool
    {
        if (! $user->is_active || ! $user->isPic()) {
            return false;
        }

        if ($task->pic_user_id !== $user->id) {
            return false;
        }

        return $task->canBeEditedByPic();
    }

    /**
     * Peserta pemilik tugas yang aktif dapat mengubah status dari assigned -> in_progress.
     */
    public function start(User $user, Task $task): bool
    {
        if (! $user->is_active || ! $user->isIntern()) {
            return false;
        }

        if ($task->intern_user_id !== $user->id) {
            return false;
        }

        return $task->canBeStartedByIntern();
    }

    /**
     * Data historis tugas tidak boleh dihapus.
     */
    public function delete(User $user, Task $task): bool
    {
        return false;
    }
}
