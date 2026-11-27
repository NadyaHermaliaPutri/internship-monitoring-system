<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $stats = [
            'total_active'   => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'pic_active'     => User::where('role', 'pic')->where('is_active', true)->count(),
            'intern_active'  => User::where('role', 'intern')->where('is_active', true)->count(),
        ];

        return view('dashboard.admin', compact('adminSummary', 'stats'));
    }

    /**
     * Dashboard Pembimbing PIC.
     */
    public function pic(Request $request): View
    {
        return view('dashboard.pic');
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
