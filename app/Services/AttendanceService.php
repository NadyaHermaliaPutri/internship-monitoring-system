<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceService
{
    public function __construct(
        protected WorkCalendarService $calendarService,
        protected MediaStorageService $mediaStorageService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Memproses check-in harian peserta magang.
     *
     * @param User $intern
     * @param UploadedFile|string $photoData (bisa file upload atau base64 webcam)
     * @param string|null $notes
     * @return Attendance
     * @throws InvalidArgumentException
     */
    public function checkIn(User $intern, UploadedFile|string $photoData, ?string $notes = null): Attendance
    {
        if (! $intern->isIntern()) {
            throw new InvalidArgumentException('Hanya pengguna dengan peran Peserta Magang yang dapat melakukan presensi.');
        }

        if (! $intern->is_active) {
            throw new InvalidArgumentException('Akun Anda telah dinonaktifkan.');
        }

        $now = $this->calendarService->getNow();
        $todayDate = $now->format('Y-m-d');

        // 1. Validasi Kalender: Hari Kerja vs Hari Libur
        if (! $this->calendarService->isWorkingDay($todayDate)) {
            $dayStatus = $this->calendarService->getDayStatus($todayDate);
            throw new InvalidArgumentException(
                "Hari ini adalah {$dayStatus['label']} ({$dayStatus['reason']}). Presensi masuk tidak diperlukan."
            );
        }

        // 2. Validasi Izin/Sakit yang sudah Disetujui
        $hasApprovedLeave = LeaveRequest::where('intern_user_id', $intern->id)
            ->activeOnDate($todayDate)
            ->exists();

        if ($hasApprovedLeave) {
            throw new InvalidArgumentException('Anda memiliki pengajuan izin/sakit yang telah disetujui untuk hari ini.');
        }

        // 3. Validasi Duplikasi: 1 presensi per peserta per tanggal
        $existing = Attendance::where('intern_user_id', $intern->id)
            ->where('attendance_date', $todayDate)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('Anda sudah melakukan presensi masuk untuk hari ini.');
        }

        // 4. Evaluasi Status Keterlambatan Berdasarkan Waktu Server Asia/Jakarta
        // Batas toleransi: 08:05:59 WIB inklusif = on_time, setelahnya = late
        $status = $this->determineStatus($now);

        // 5. Simpan transaksi (MediaFile + Attendance) dengan penanganan race condition
        try {
            return DB::transaction(function () use ($intern, $photoData, $todayDate, $now, $status, $notes) {
                if ($photoData instanceof UploadedFile) {
                    $media = $this->mediaStorageService->storeUploadedFile($photoData, $intern, 'attendance_photo');
                } else {
                    $media = $this->mediaStorageService->storeBase64Image($photoData, $intern, 'attendance_photo');
                }

                return Attendance::create([
                    'intern_user_id' => $intern->id,
                    'attendance_date' => $todayDate,
                    'checked_in_at' => $now,
                    'status' => $status,
                    'photo_media_id' => $media->id,
                    'reviewed_by' => null,
                    'notes' => $notes,
                ]);
            });
        } catch (QueryException $e) {
            // Error code 23000 adalah integrity constraint violation (duplicate key)
            if ($e->getCode() === '23000') {
                throw new InvalidArgumentException('Anda sudah melakukan presensi masuk untuk hari ini.');
            }
            throw $e;
        }
    }

    /**
     * Menentukan status ketepatan waktu berdasarkan jam server WIB.
     * Batas: $\le$ 08:05:59 WIB = on_time, setelahnya = late.
     */
    public function determineStatus(Carbon $checkedInAt): string
    {
        $cutoffHour = 8;
        $cutoffMinute = 5;

        // Ambil waktu cutoff hari tersebut pukul 08:05:59
        $cutoff = $checkedInAt->copy()->setTime($cutoffHour, $cutoffMinute, 59);

        return $checkedInAt->lessThanOrEqualTo($cutoff) ? 'on_time' : 'late';
    }

    /**
     * Koreksi / Peninjauan presensi oleh Admin/HR.
     */
    public function reviewOrCorrectAttendance(
        Attendance $attendance,
        User $admin,
        string $newStatus,
        ?string $reviewNotes = null
    ): Attendance {
        if (! $admin->isAdmin()) {
            throw new InvalidArgumentException('Hanya Admin/HR yang berwenang meninjau atau mengoreksi presensi.');
        }

        if (! in_array($newStatus, ['on_time', 'late'], true)) {
            throw new InvalidArgumentException("Status '{$newStatus}' tidak valid. Harus on_time atau late.");
        }

        $oldValues = [
            'status' => $attendance->status,
            'notes' => $attendance->notes,
            'reviewed_by' => $attendance->reviewed_by,
        ];

        $attendance->update([
            'status' => $newStatus,
            'reviewed_by' => $admin->id,
            'notes' => $reviewNotes ?: $attendance->notes,
        ]);

        $newValues = [
            'status' => $attendance->status,
            'notes' => $attendance->notes,
            'reviewed_by' => $attendance->reviewed_by,
        ];

        // Audit Trail penting
        $this->auditLogService->log(
            action: 'attendance_corrected',
            entityType: 'attendance',
            entityId: $attendance->id,
            oldValues: $oldValues,
            newValues: $newValues,
            actor: $admin
        );

        return $attendance->fresh();
    }
}
