<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EwsController;
use App\Http\Controllers\InternProfileController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/media/private/{mediaFile}', [PrivateMediaController::class, 'show'])->name('media.private.show');

    // ==============================================================
    // 1. DASHBOARD & MANAJEMEN SISTEM ADMINISTRATOR / HR
    // ==============================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Early Warning System (EWS) - Modul 4
        Route::get('/ews', [EwsController::class, 'adminIndex'])->name('ews.index');
        Route::put('/ews/settings', [EwsController::class, 'adminUpdateSettings'])->name('ews.settings.update');

        // Manajemen Akun Pengguna (CRUD)
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Pengelolaan Profil Khusus Peserta Magang
        Route::get('intern-profiles', [InternProfileController::class, 'index'])->name('intern-profiles.index');
        Route::get('intern-profiles/{user}/edit', [InternProfileController::class, 'edit'])->name('intern-profiles.edit');
        Route::put('intern-profiles/{user}', [InternProfileController::class, 'update'])->name('intern-profiles.update');

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

        // Early Warning System (EWS) - Modul 4
        Route::get('/ews', [EwsController::class, 'picIndex'])->name('ews.index');
        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'picIndex'])->name('index');
        });
        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'picIndex'])->name('index');
        });
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::resource('tasks', TaskController::class);
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
        Route::resource('logbooks', LogbookController::class);
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
    });
});