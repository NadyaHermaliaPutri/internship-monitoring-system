<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PrivateMediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Otentikasi dan Dashboard
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

    // Dashboard Admin / HR
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.admin');
        })->name('dashboard');
    });

    // Dashboard PIC
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.pic');
        })->name('dashboard');
    });

    // Dashboard Peserta Magang
    Route::middleware('role:intern')->prefix('intern')->name('intern.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.intern');
        })->name('dashboard');
    });
});
