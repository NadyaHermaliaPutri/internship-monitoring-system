<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login.
     * Jika pengguna sudah login, langsung alihkan ke dashboard sesuai role.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            return redirect()->to($user->getDashboardRoute());
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Eksekusi autentikasi & validasi status aktif
        $request->authenticate();

        // 2. Regenerasi ID sesi untuk mencegah serangan Session Fixation
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 3. Alihkan pengguna sesuai dengan role masing-masing
        return redirect()->intended($user->getDashboardRoute());
    }

    /**
     * Proses logout dan hancurkan sesi secara aman.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 1. Lepaskan otentikasi dari guard
        Auth::logout();

        // 2. Hancurkan data sesi dan regenerasi session id
        $request->session()->invalidate();

        // 3. Regenerasi token CSRF
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
