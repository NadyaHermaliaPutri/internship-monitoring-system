<?php

namespace App\Policies;

use App\Models\Logbook;
use App\Models\LogbookRevision;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\TaskSubmission;
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

        // 3. Hak akses PIC: dilarang untuk attendance_photo & leave_evidence; diizinkan untuk task_evidence, logbook_evidence, task_attachment
        if ($user->isPic()) {
            if (in_array($mediaFile->category, ['attendance_photo', 'leave_evidence'], true)) {
                return false;
            }

            if ($mediaFile->category === 'task_evidence') {
                return TaskSubmission::query()
                    ->where('evidence_media_id', $mediaFile->id)
                    ->whereHas('task', function ($q) use ($user) {
                        $q->where('pic_user_id', $user->id);
                    })
                    ->exists();
            } elseif ($mediaFile->category === 'logbook_evidence') {
                $logbook = Logbook::query()
                    ->where('evidence_media_id', $mediaFile->id)
                    ->first();

                if ($logbook && $logbook->isViewableByPic($user->id)) {
                    return true;
                }

                $revision = LogbookRevision::query()
                    ->with('logbook')
                    ->where('evidence_media_id', $mediaFile->id)
                    ->first();

                if ($revision?->logbook && $revision->logbook->isViewableByPic($user->id)) {
                    return true;
                }
            } elseif ($mediaFile->category === 'task_attachment') {
                return Task::query()
                    ->where('attachment_media_id', $mediaFile->id)
                    ->where('pic_user_id', $user->id)
                    ->exists();
            }
        }

        // 4. Hak akses peserta penerima tugas untuk mengunduh/melihat lampiran tugas dari PIC
        if ($user->isIntern() && $mediaFile->category === 'task_attachment') {
            return Task::query()
                ->where('attachment_media_id', $mediaFile->id)
                ->where('intern_user_id', $user->id)
                ->exists();
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
     * Penghapusan file hanya oleh Admin atau pemilik, KECUALI untuk kategori task_evidence,
     * logbook_evidence, dan task_attachment yang merupakan bagian dari riwayat append-only.
     */
    public function delete(User $user, MediaFile $mediaFile): bool
    {
        if (in_array($mediaFile->category, ['task_evidence', 'logbook_evidence', 'task_attachment'], true)) {
            return false;
        }

        return $user->isAdmin() || $user->id === $mediaFile->owner_user_id;
    }
}
