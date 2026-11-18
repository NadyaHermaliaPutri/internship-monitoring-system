<?php

namespace App\Http\Controllers;

use App\Services\AttendanceRecapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected AttendanceRecapService $recapService
    ) {}

    /**
     * Dashboard Administrator / HR.
     */
    public function admin(Request $request): View
    {
        $adminSummary = $this->recapService->getAdminDailyDashboard();

        return view('dashboard.admin', compact('adminSummary'));
    }

    /**
     * Dashboard Pembimbing PIC.
     */
    public function pic(Request $request): View
    {
        $mentoringSummary = $this->recapService->getPicDailyDashboard($request->user());

        return view('dashboard.pic', compact('mentoringSummary'));
    }

    /**
     * Dashboard Peserta Magang (Intern).
     */
    public function intern(Request $request): View
    {
        $dailySummary = $this->recapService->getInternDailyDashboard($request->user());

        return view('dashboard.intern', compact('dailySummary'));
    }
}
