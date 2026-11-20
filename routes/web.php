<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskReviewController;
use App\Http\Controllers\TaskSubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Otentikasi, Dashboard, Presensi, Kalender, Izin, Logbook, & Tugas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Route Tamu (Hanya dapat diakses jika belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

// Route Terotentikasi
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Akses Berkas Privat Terotorisasi (tanpa membuat link publik)
    Route::get('/media/private/{mediaFile}', [PrivateMediaController::class, 'show'])
        ->name('media.private.show');

    // Dashboard & Monitoring Admin / HR
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.admin');
        })->name('dashboard');

        // Modul 2: Kalender Kerja (Admin/HR)
        Route::prefix('kalender')->name('calendar.')->group(function () {
            Route::get('/', [CalendarController::class, 'index'])->name('index');
            Route::post('/', [CalendarController::class, 'store'])->name('store');
            Route::delete('/{calendarException}', [CalendarController::class, 'destroy'])->name('destroy');
        });

        // Modul 2: Monitoring & Koreksi Presensi (Admin/HR)
        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'adminIndex'])->name('index');
            Route::patch('/{attendance}', [AttendanceController::class, 'adminUpdate'])->name('update');
        });

        // Modul 2: Verifikasi & Keputusan Izin/Sakit (Admin/HR)
        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'adminIndex'])->name('index');
            Route::post('/{leaveRequest}/review', [LeaveRequestController::class, 'adminReview'])->name('review');
        });

        // Modul 3: Monitoring Logbook (Admin/HR)
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    });

    // Dashboard, Logbook, & Pengelolaan Tugas PIC
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.pic');
        })->name('dashboard');

        // Modul 2: Monitoring Presensi Peserta Bimbingan (Read-Only PIC)
        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'picIndex'])->name('index');
        });

        // Modul 2: Pemantauan Izin/Sakit Peserta Bimbingan (Read-Only PIC)
        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'picIndex'])->name('index');
        });

        // Modul 3: Monitoring Logbook Peserta Bimbingan (PIC)
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/reviews', [TaskReviewController::class, 'store'])->name('tasks.reviews.store');
    });

    // Dashboard, Logbook, & Daftar Tugas Peserta Magang
    Route::middleware('role:intern')->prefix('intern')->name('intern.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.intern');
        })->name('dashboard');

        // Modul 2: Presensi Masuk & Riwayat (Peserta Magang)
        Route::prefix('presensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'internIndex'])->name('index');
            Route::post('/', [AttendanceController::class, 'internStore'])->name('store');
        });

        // Modul 2: Pengajuan & Riwayat Izin/Sakit (Peserta Magang)
        Route::prefix('izin')->name('leave.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'internIndex'])->name('index');
            Route::post('/', [LeaveRequestController::class, 'internStore'])->name('store');
        });

        // Modul 3: Pengisian & Manajemen Logbook (Peserta Magang)
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/create', [LogbookController::class, 'create'])->name('logbooks.create');
        Route::post('/logbooks', [LogbookController::class, 'store'])->name('logbooks.store');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::get('/logbooks/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbooks.edit');
        Route::put('/logbooks/{logbook}', [LogbookController::class, 'update'])->name('logbooks.update');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
        Route::post('/tasks/{task}/submissions', [TaskSubmissionController::class, 'store'])->name('tasks.submissions.store');
    });
});
