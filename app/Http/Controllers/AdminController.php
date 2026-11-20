<?php

namespace App\Http\Controllers;

use App\Models\PicProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard monitoring magang untuk Admin / HR.
     * Mengambil data statistik dan rekapitulasi secara dinamis dari database.
     */
    public function dashboard(): View
    {
        $now = Carbon::now();

        // 1. Statistik Utama Dinamis dari Database (Zero Hardcoded Data)
        $stats = [
            'active_interns' => User::where('role', 'intern')->where('is_active', true)->count(),
            'active_pics'    => User::where('role', 'pic')->where('is_active', true)->count(),
            'total_active'   => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'new_this_month' => User::where('role', 'intern')
                                    ->whereMonth('created_at', $now->month)
                                    ->whereYear('created_at', $now->year)
                                    ->count(),
            'pic_divisions'  => PicProfile::distinct()->pluck('department')->filter()->take(3)->implode(', ') ?: 'Divisi Internal',
            'near_end_interns' => User::where('role', 'intern')
                                    ->where('is_active', true)
                                    ->whereHas('internProfile', function ($q) use ($now) {
                                        $q->whereBetween('internship_end_date', [$now, $now->copy()->addDays(30)]);
                                    })
                                    ->count(),
        ];

        // 2. Daftar Peserta Magang Terbaru (Eager Loading untuk Mencegah N+1 Problem)
        $recentInterns = User::where('role', 'intern')
            ->with(['internProfile', 'picProfile'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentInterns'));
    }
}
