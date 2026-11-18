<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveRequestService
{
    public function __construct(
        protected WorkCalendarService $calendarService,
        protected MediaStorageService $mediaStorageService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Membuat permohonan izin/sakit baru oleh peserta magang.
     */
    public function submitRequest(
        User $intern,
        string $leaveType,
        string $startDate,
        string $endDate,
        string $reason,
        ?UploadedFile $evidenceFile = null
    ): LeaveRequest {
        if (! $intern->isIntern()) {
            throw new InvalidArgumentException('Hanya peserta magang yang dapat mengajukan permohonan izin/sakit.');
        }

        if (! in_array($leaveType, ['leave', 'sick'], true)) {
            throw new InvalidArgumentException("Jenis permohonan '{$leaveType}' tidak valid. Pilihan: leave atau sick.");
        }

        $start = $this->calendarService->parseDate($startDate);
        $end = $this->calendarService->parseDate($endDate);

        if ($start->greaterThan($end)) {
            throw new InvalidArgumentException('Tanggal mulai tidak boleh melebihi tanggal selesai.');
        }

        // Cek overlap dengan permohonan aktif lain (pending atau approved)
        $hasOverlap = LeaveRequest::where('intern_user_id', $intern->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($start, $end) {
                $query->whereDate('start_date', '<=', $end->format('Y-m-d'))
                    ->whereDate('end_date', '>=', $start->format('Y-m-d'));
            })
            ->exists();

        if ($hasOverlap) {
            throw new InvalidArgumentException('Anda sudah memiliki pengajuan izin/sakit yang aktif atau sedang menunggu review pada rentang tanggal tersebut.');
        }

        return DB::transaction(function () use ($intern, $leaveType, $start, $end, $reason, $evidenceFile) {
            $evidenceMediaId = null;

            if ($evidenceFile) {
                $media = $this->mediaStorageService->storeUploadedFile($evidenceFile, $intern, 'leave_evidence');
                $evidenceMediaId = $media->id;
            }

            return LeaveRequest::create([
                'intern_user_id' => $intern->id,
                'leave_type' => $leaveType,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'reason' => $reason,
                'status' => 'pending',
                'evidence_media_id' => $evidenceMediaId,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
            ]);
        });
    }

    /**
     * Memproses persetujuan atau penolakan pengajuan oleh Admin/HR.
     */
    public function reviewRequest(
        LeaveRequest $leaveRequest,
        User $admin,
        string $decision, // 'approved' atau 'rejected'
        ?string $reviewNotes = null
    ): LeaveRequest {
        if (! $admin->isAdmin()) {
            throw new InvalidArgumentException('Hanya Admin/HR yang memiliki wewenang menyetujui atau menolak permohonan izin/sakit. PIC tidak memiliki izin ini.');
        }

        if (! $leaveRequest->isPending()) {
            throw new InvalidArgumentException("Permohonan izin ini sudah diproses sebelumnya dengan status '{$leaveRequest->status}'.");
        }

        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw new InvalidArgumentException("Keputusan '{$decision}' tidak valid. Harus approved atau rejected.");
        }

        $oldValues = [
            'status' => $leaveRequest->status,
            'reviewed_by' => $leaveRequest->reviewed_by,
            'reviewed_at' => $leaveRequest->reviewed_at?->toDateTimeString(),
            'review_notes' => $leaveRequest->review_notes,
        ];

        $now = $this->calendarService->getNow();

        $leaveRequest->update([
            'status' => $decision,
            'reviewed_by' => $admin->id,
            'reviewed_at' => $now,
            'review_notes' => $reviewNotes,
        ]);

        $newValues = [
            'status' => $leaveRequest->status,
            'reviewed_by' => $leaveRequest->reviewed_by,
            'reviewed_at' => $leaveRequest->reviewed_at->toDateTimeString(),
            'review_notes' => $leaveRequest->review_notes,
        ];

        // Catat jejak audit keputusan
        $actionName = $decision === 'approved' ? 'leave_request_approved' : 'leave_request_rejected';
        $this->auditLogService->log(
            action: $actionName,
            entityType: 'leave_requests',
            entityId: $leaveRequest->id,
            oldValues: $oldValues,
            newValues: $newValues,
            actor: $admin
        );

        return $leaveRequest->fresh();
    }
}
