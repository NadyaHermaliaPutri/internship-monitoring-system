<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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

        // 3. Jika "Ingat Saya" tidak dicentang, pastikan tidak ada sisa cookie recaller lama
        if (! $request->boolean('remember')) {
            Cookie::queue(Cookie::forget(Auth::guard('web')->getRecallerName()));
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 4. Alihkan pengguna sesuai dengan role masing-masing
        return redirect()->to($user->getDashboardRoute());
    }

    /**
     * Proses logout dan hancurkan sesi secara aman.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('web')->user();

        // 1. Cabut remember_token di database agar cookie recaller lama tidak lagi valid
        if ($user) {
            $user->forceFill(['remember_token' => null])->save();
        }

        // 2. Lepaskan otentikasi dari guard web (sekali gus mengantrekan penghapusan cookie remember me)
        Auth::guard('web')->logout();

        // 3. Hancurkan data sesi dan regenerasi session id
        $request->session()->invalidate();

        // 4. Regenerasi token CSRF
        $request->session()->regenerateToken();

        // 5. Pastikan cookie remember me dan cookie sesi dicabut di sisi browser
        $recallerCookie = Cookie::forget(Auth::guard('web')->getRecallerName());

        return redirect()
            ->route('login')
            ->withCookie($recallerCookie)
            ->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
