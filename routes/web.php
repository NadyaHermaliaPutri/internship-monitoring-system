<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EwsController;
use App\Http\Controllers\InternProfileController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PicProfileController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskReviewController;
use App\Http\Controllers\TaskSubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (! $user->is_active) {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
        ]);
    }

    $dashboardUrl = $user->getDashboardRoute();

    if ($dashboardUrl === '/') {
        abort(403, 'Peran pengguna tidak valid.');
    }

    return redirect()->to($dashboardUrl);
});

// ==============================================================
// PWA ASSETS & SERVICE WORKER (AKSES PUBLIK)
// ==============================================================
Route::get('/manifest.webmanifest', function () {
    return response()->file(public_path('manifest.webmanifest'), [
        'Content-Type' => 'application/manifest+json',
    ]);
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Service-Worker-Allowed' => '/',
    ]);
})->name('pwa.sw');

Route::get('/offline.html', function () {
    return response()->file(public_path('offline.html'), [
        'Content-Type' => 'text/html',
    ]);
})->name('pwa.offline');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/media/private/{mediaFile}', [PrivateMediaController::class, 'show'])->name('media.private.show');

    // Notifikasi In-App (Database Notifications)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // ==============================================================
    // 1. DASHBOARD & MANAJEMEN SISTEM ADMINISTRATOR / HR
    // ==============================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Early Warning System (EWS) - Modul 4
        Route::get('/ews', [EwsController::class, 'adminIndex'])->name('ews.index');
        Route::put('/ews/settings', [EwsController::class, 'adminUpdateSettings'])->name('ews.settings.update');

        // Laporan & Rekapitulasi (PDF / Excel) - Modul 4
        Route::prefix('laporan')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
        });

        // Manajemen Akun Pengguna (CRUD)
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Pengelolaan Profil Khusus Peserta Magang
        Route::get('intern-profiles', [InternProfileController::class, 'index'])->name('intern-profiles.index');
        Route::get('intern-profiles/{user}/edit', [InternProfileController::class, 'edit'])->name('intern-profiles.edit');
        Route::put('intern-profiles/{user}', [InternProfileController::class, 'update'])->name('intern-profiles.update');

        // Pengelolaan Profil Khusus PIC (Pembimbing)
        Route::get('pic-profiles', [PicProfileController::class, 'index'])->name('pic-profiles.index');
        Route::put('pic-profiles/{user}', [PicProfileController::class, 'update'])->name('pic-profiles.update');

        // Modul 2 & 3 (Admin)
        Route::prefix('kalender')->name('calendar.')->group(function () {
            Route::get('/', [CalendarController::class, 'index'])->name('index');
            Route::post('/', [CalendarController::class, 'store'])->name('store');
            Route::delete('/{calendarException}', [CalendarController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'adminIndex'])->name('index');
            Route::patch('/{attendance}', [AttendanceController::class, 'adminUpdate'])->name('update');
            Route::post('/{attendance}/reject', [AttendanceController::class, 'adminReject'])->name('reject');
        });

        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'adminIndex'])->name('index');
            Route::post('/{leaveRequest}/review', [LeaveRequestController::class, 'adminReview'])->name('review');
        });

        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    });

    // ==============================================================
    // 2. MODUL PIC
    // ==============================================================
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'pic'])->name('dashboard');

        // Modul 3: Monitoring & Review Logbook Peserta Bimbingan (PIC)
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::post('/logbooks/{logbook}/review', [LogbookController::class, 'review'])->name('logbooks.review');

        // Modul 3: Pembuatan, Pengelolaan, & Review Tugas (PIC)
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/reviews', [TaskReviewController::class, 'store'])->name('tasks.reviews.store');
    });

    // ==============================================================
    // 3. MODUL PESERTA MAGANG
    // ==============================================================
    Route::middleware('role:intern')->prefix('intern')->name('intern.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'intern'])->name('dashboard');
        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'internIndex'])->name('index');
            Route::post('/', [AttendanceController::class, 'internStore'])->name('store');
        });
        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'internIndex'])->name('index');
            Route::post('/', [LeaveRequestController::class, 'internStore'])->name('store');
        });

        // Modul 3: Pengisian & Manajemen Logbook (Peserta Magang — Tanpa Destroy)
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/create', [LogbookController::class, 'create'])->name('logbooks.create');
        Route::post('/logbooks', [LogbookController::class, 'store'])->name('logbooks.store');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::get('/logbooks/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbooks.edit');
        Route::put('/logbooks/{logbook}', [LogbookController::class, 'update'])->name('logbooks.update');
        Route::patch('/logbooks/{logbook}/complete', [LogbookController::class, 'complete'])->name('logbooks.complete');

        // Modul 3: Daftar, Mulai, & Pengumpulan Tugas (Peserta Magang)
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
        Route::post('/tasks/{task}/submissions', [TaskSubmissionController::class, 'store'])->name('tasks.submissions.store');
    });
});
