<?php

namespace App\Http\Controllers;

use App\Http\Requests\Leave\ReviewLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Models\PicAssignment;
use App\Services\LeaveRequestService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | SISI PESERTA MAGANG (INTERN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan form pengajuan dan daftar riwayat izin/sakit peserta.
     */
    public function internIndex(Request $request): View
    {
        $intern = $request->user();
        $statusFilter = $request->input('status');

        $query = LeaveRequest::with(['evidence', 'reviewer'])
            ->where('intern_user_id', $intern->id)
            ->orderByDesc('created_at');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        $leaveRequests = $query->paginate(8)->withQueryString();

        // Ringkasan permohonan peserta
        $totalPending = LeaveRequest::where('intern_user_id', $intern->id)->pending()->count();
        $totalApproved = LeaveRequest::where('intern_user_id', $intern->id)->approved()->count();
        $totalRejected = LeaveRequest::where('intern_user_id', $intern->id)->rejected()->count();

        return view('intern.leave.index', compact(
            'intern',
            'leaveRequests',
            'statusFilter',
            'totalPending',
            'totalApproved',
            'totalRejected'
        ));
    }

    /**
     * Memproses pengajuan izin/sakit baru oleh peserta magang.
     */
    public function internStore(StoreLeaveRequest $request): RedirectResponse
    {
        $intern = $request->user();

        try {
            $this->leaveService->submitRequest(
                intern: $intern,
                leaveType: $request->leave_type,
                startDate: $request->start_date,
                endDate: $request->end_date,
                reason: $request->reason,
                evidenceFile: $request->file('evidence')
            );

            return redirect()
                ->route('intern.leave.index')
                ->with('success', 'Permohonan izin/sakit berhasil dikirim dengan status Menunggu (Pending) verifikasi HR.');
        } catch (Exception $e) {
            return redirect()
                ->route('intern.leave.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SISI ADMINISTRATOR / HR (ADMIN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar review seluruh permohonan izin/sakit untuk Admin/HR.
     */
    public function adminIndex(Request $request): View
    {
        $statusFilter = $request->input('status');
        $typeFilter = $request->input('type');
        $search = $request->input('search');

        $query = LeaveRequest::with(['intern', 'evidence', 'reviewer'])
            ->orderByDesc('created_at');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($typeFilter && in_array($typeFilter, ['leave', 'sick'], true)) {
            $query->where('leave_type', $typeFilter);
        }

        if ($search) {
            $query->whereHas('intern', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaveRequests = $query->paginate(12)->withQueryString();

        // Statistik keseluruhan
        $totalPending = LeaveRequest::pending()->count();
        $totalApproved = LeaveRequest::approved()->count();
        $totalRejected = LeaveRequest::rejected()->count();
        $totalAll = LeaveRequest::count();

        return view('admin.leave.index', compact(
            'leaveRequests',
            'statusFilter',
            'typeFilter',
            'search',
            'totalPending',
            'totalApproved',
            'totalRejected',
            'totalAll'
        ));
    }

    /**
     * Memproses persetujuan atau penolakan pengajuan izin oleh Admin/HR.
     */
    public function adminReview(ReviewLeaveRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        Gate::authorize('review', $leaveRequest);

        try {
            $validated = $request->validated();

            $this->leaveService->reviewRequest(
                leaveRequest: $leaveRequest,
                admin: $request->user(),
                decision: $validated['decision'],
                reviewNotes: $validated['review_notes'] ?? null
            );

            $message = $validated['decision'] === 'approved'
                ? 'Pengajuan izin/sakit berhasil disetujui.'
                : 'Pengajuan izin/sakit berhasil ditolak.';

            return redirect()
                ->back()
                ->with('success', $message);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SISI PEMBIMBING PIC (READ-ONLY)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar permohonan izin/sakit peserta bimbingan aktif PIC (read-only).
     */
    public function picIndex(Request $request): View
    {
        $pic = $request->user();

        // Ambil ID peserta yang saat ini aktif ditugaskan ke PIC ini (end_date IS NULL)
        $assignedInternUserIds = PicAssignment::active()
            ->where('pic_user_id', $pic->id)
            ->pluck('intern_user_id')
            ->unique()
            ->toArray();

        $statusFilter = $request->input('status');
        $typeFilter = $request->input('type');

        $query = LeaveRequest::with(['intern', 'evidence', 'reviewer'])
            ->whereIn('intern_user_id', $assignedInternUserIds)
            ->orderByDesc('created_at');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($typeFilter && in_array($typeFilter, ['leave', 'sick'], true)) {
            $query->where('leave_type', $typeFilter);
        }

        $leaveRequests = $query->paginate(12)->withQueryString();

        return view('pic.leave.index', compact(
            'leaveRequests',
            'statusFilter',
            'typeFilter',
            'assignedInternUserIds'
        ));
    }
}
