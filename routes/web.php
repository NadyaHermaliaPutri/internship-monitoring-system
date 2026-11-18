<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LogbookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Otentikasi, Dashboard, dan Logbook Harian
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

    // Dashboard & Monitoring Admin / HR
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.admin');
        })->name('dashboard');

        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
    });

    // Dashboard & Monitoring PIC
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.pic');
        })->name('dashboard');

        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
    });

    // Dashboard & Fitur Peserta Magang
    Route::middleware('role:intern')->prefix('intern')->name('intern.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.intern');
        })->name('dashboard');

        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/logbooks/create', [LogbookController::class, 'create'])->name('logbooks.create');
        Route::post('/logbooks', [LogbookController::class, 'store'])->name('logbooks.store');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::get('/logbooks/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbooks.edit');
        Route::put('/logbooks/{logbook}', [LogbookController::class, 'update'])->name('logbooks.update');
    });
});
