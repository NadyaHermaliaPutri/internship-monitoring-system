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

        // 2. Cari presensi hari ini jika sudah pernah tercatat
        $existing = Attendance::where('intern_user_id', $intern->id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        $isResubmission = $existing && $existing->verification_status === 'rejected';

        // 3. Validasi Izin/Sakit yang sudah Disetujui (hanya untuk check-in awal, bukan resubmission presensi ditolak)
        if (! $isResubmission) {
            $hasApprovedLeave = LeaveRequest::where('intern_user_id', $intern->id)
                ->activeOnDate($todayDate)
                ->exists();

            if ($hasApprovedLeave) {
                throw new InvalidArgumentException('Anda memiliki pengajuan izin/sakit yang telah disetujui untuk hari ini.');
            }

            if ($existing) {
                throw new InvalidArgumentException('Anda sudah melakukan presensi masuk untuk hari ini.');
            }
        }

        if ($isResubmission) {
            // Kasus Resubmission: Presensi hari ini sebelumnya ditolak HR, peserta foto ulang
            return DB::transaction(function () use ($intern, $photoData, $existing) {
                if ($photoData instanceof UploadedFile) {
                    $media = $this->mediaStorageService->storeUploadedFile($photoData, $intern, 'attendance_photo');
                } else {
                    $media = $this->mediaStorageService->storeBase64Image($photoData, $intern, 'attendance_photo');
                }

                $oldPhotoMediaId = $existing->photo_media_id;
                $oldNotes = $existing->notes;
                $oldVerificationStatus = $existing->verification_status;

                // Update record yang sama: ganti photo_media_id, set verification_status ke 'submitted'
                // checked_in_at dan status (on_time/late) TETAP MEMPERTAHANKAN WAKTU DAN STATUS AWAL
                $existing->update([
                    'photo_media_id' => $media->id,
                    'verification_status' => 'submitted',
                    'reviewed_by' => null,
                    'notes' => null,
                ]);

                // Catat ke audit log
                $this->auditLogService->log(
                    action: 'attendance_resubmitted',
                    entityType: 'attendance',
                    entityId: $existing->id,
                    oldValues: [
                        'photo_media_id' => $oldPhotoMediaId,
                        'verification_status' => $oldVerificationStatus,
                        'rejection_notes' => $oldNotes,
                    ],
                    newValues: [
                        'photo_media_id' => $media->id,
                        'verification_status' => 'submitted',
                    ],
                    actor: $intern
                );

                return $existing->fresh();
            });
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
                    'verification_status' => 'submitted',
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

    /**
     * Menolak presensi oleh Admin/HR (misal foto buram, manipulasi, atau tidak valid).
     *
     * @param Attendance $attendance
     * @param User $admin
     * @param string $reason
     * @param string|null $ipAddress
     * @return Attendance
     * @throws InvalidArgumentException
     */
    public function rejectAttendance(
        Attendance $attendance,
        User $admin,
        string $reason,
        ?string $ipAddress = null
    ): Attendance {
        if (! $admin->isAdmin()) {
            throw new InvalidArgumentException('Hanya Admin/HR yang berwenang menolak presensi.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan penolakan presensi wajib diisi.');
        }

        $oldValues = [
            'verification_status' => $attendance->verification_status,
            'notes' => $attendance->notes,
            'reviewed_by' => $attendance->reviewed_by,
        ];

        $attendance->update([
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => $reason,
        ]);

        $newValues = [
            'verification_status' => 'rejected',
            'notes' => $reason,
            'reviewed_by' => $admin->id,
        ];

        // Audit Trail Penolakan
        $this->auditLogService->log(
            action: 'attendance_rejected',
            entityType: 'attendance',
            entityId: $attendance->id,
            oldValues: $oldValues,
            newValues: $newValues,
            actor: $admin
        );

        return $attendance->fresh();
    }
}
