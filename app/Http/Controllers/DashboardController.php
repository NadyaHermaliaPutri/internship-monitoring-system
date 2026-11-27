<?php

namespace App\Http\Controllers;

use App\Services\AttendanceRecapService;
use App\Services\DashboardService;
use App\Services\InternDashboardService;
use App\Services\PicDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AttendanceRecapService $recapService,
        protected InternDashboardService $internDashboardService,
        protected PicDashboardService $picDashboardService
    ) {}

    /**
     * Dashboard Administrator / HR.
     */
    public function admin(Request $request): View
    {
        $search = $request->query('search');
        $data = $this->dashboardService->getAdminDashboardData($search);

        $adminSummary = $data['adminSummary'];
        $stats = $data['stats'];
        $taskSummary = $data['taskSummary'];
        $latestLogbooks = $data['latestLogbooks'];
        $ewsSummary = $data['ewsSummary'];

        return view('dashboard.admin', compact(
            'data',
            'adminSummary',
            'stats',
            'taskSummary',
            'latestLogbooks',
            'ewsSummary',
            'search'
        ));
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
