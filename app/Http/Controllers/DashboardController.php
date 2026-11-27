<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AttendanceRecapService;
use App\Services\InternDashboardService;
use App\Services\PicDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected AttendanceRecapService $recapService,
        protected InternDashboardService $internDashboardService,
        protected PicDashboardService $picDashboardService
    ) {}

    /**
     * Dashboard Administrator / HR.
     */
    public function admin(Request $request): View
    {
        $adminSummary = $this->recapService->getAdminDailyDashboard();

        $stats = [
            'total_active' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'pic_active' => User::where('role', 'pic')->where('is_active', true)->count(),
            'intern_active' => User::where('role', 'intern')->where('is_active', true)->count(),
        ];

        return view('dashboard.admin', compact('adminSummary', 'stats'));
    }

    /**
     * Dashboard Pembimbing PIC.
     */
    public function pic(Request $request): View
    {
        $picDashboard = $this->picDashboardService->getSummary($request->user());

        return view('dashboard.pic', compact('picDashboard'));
    }

    /**
     * Dashboard Peserta Magang (Intern).
     */
    public function intern(Request $request): View
    {
        $dailySummary = $this->recapService->getInternDailyDashboard($request->user());
        $internDashboard = $this->internDashboardService->getSummary($request->user());

        return view('dashboard.intern', compact('dailySummary', 'internDashboard'));
    }
}
