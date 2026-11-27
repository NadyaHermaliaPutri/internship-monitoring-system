<?php

namespace App\Http\Controllers;

use App\Models\PicProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PicProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar profil PIC (Pembimbing) beserta ringkasan metrik.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'pic')->with('picProfile');

        // Pencarian (Nama atau Email)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Statistik Metrik PIC
        $allPics = User::where('role', 'pic')->with('picProfile')->get();
        $stats = [
            'total_pic'      => $allPics->count(),
            'total_profiles' => PicProfile::count(),
            'active_pic'     => $allPics->where('is_active', true)->count(),
        ];

        $pics = $query->latest()->paginate(10)->withQueryString();

        return view('admin.pic_profiles.index', compact('pics', 'stats'));
    }

    /**
     * Memperbarui data profil PIC.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Pastikan akun target benar-benar memiliki role PIC
        if (! $user->isPic()) {
            return redirect()
                ->route('admin.pic-profiles.index')
                ->with('error', "Pengguna {$user->name} bukan akun PIC (Pembimbing).");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Nama PIC wajib diisi.',
            'name.max'      => 'Nama PIC maksimal 150 karakter.',
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

        $user->picProfile()->updateOrCreate(
            ['user_id' => $user->id],
            []
        );

        return redirect()
            ->route('admin.pic-profiles.index')
            ->with('success', "Profil PIC {$user->name} berhasil diperbarui.");
    }
}
