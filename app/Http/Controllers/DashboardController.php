<?php

namespace App\Http\Controllers;

use App\Services\AttendanceRecapService;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AttendanceRecapService $recapService
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
        $search = $request->query('search');
        $data = $this->dashboardService->getPicDashboardData($request->user(), $search);

        $mentoringSummary = $data['mentoringSummary'];
        $taskStats = $data['taskStats'];
        $awaitingReviewTasks = $data['awaitingReviewTasks'];
        $internsWithTasks = $data['internsWithTasks'];
        $ewsSummary = $data['ewsSummary'];
        $upcomingDeadlines = $data['upcomingDeadlines'];

        return view('dashboard.pic', compact(
            'data',
            'mentoringSummary',
            'taskStats',
            'awaitingReviewTasks',
            'internsWithTasks',
            'ewsSummary',
            'upcomingDeadlines',
            'search'
        ));
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
