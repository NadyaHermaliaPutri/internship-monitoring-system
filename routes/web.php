<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InternProfileController;
use App\Http\Controllers\UserController;
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

    // Dashboard Admin / HR & Manajemen Sistem
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Manajemen Akun Pengguna (CRUD)
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Pengelolaan Profil Khusus Peserta Magang (Admin/HR)
        Route::get('intern-profiles', [InternProfileController::class, 'index'])->name('intern-profiles.index');
        Route::get('intern-profiles/{user}/edit', [InternProfileController::class, 'edit'])->name('intern-profiles.edit');
        Route::put('intern-profiles/{user}', [InternProfileController::class, 'update'])->name('intern-profiles.update');
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
