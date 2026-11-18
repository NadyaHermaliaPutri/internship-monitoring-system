<?php

namespace App\Policies;

use App\Models\CalendarException;
use App\Models\User;

class CalendarExceptionPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar kalender kerja.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat membuat pengecualian kalender.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui pengecualian kalender.
     */
    public function update(User $user, CalendarException $calendarException): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus pengecualian kalender.
     */
    public function delete(User $user, CalendarException $calendarException): bool
    {
        return $user->isAdmin();
    }
}
