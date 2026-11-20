@extends('layouts.app')

@section('title', 'Edit Akun Pengguna')

@section('page-title', 'Kelola Akun Pengguna')
@section('page-subtitle', 'PT WAN Teknologi Internasional – Pengelolaan Hak Akses & Penugasan PIC')

@section('content')
<div class="flex items-start justify-center min-h-[70vh]">
    <div class="w-full max-w-lg">

        <!-- Card Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">

            <!-- Judul & Subjudul -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">Edit Akun Pengguna</h2>
                <p class="text-sm text-[#015987] font-medium mt-1">Perbarui informasi akun pengguna di bawah ini.</p>
            </div>

            <!-- Peringatan jika mengedit akun sendiri -->
            @if(auth()->id() === $user->id)
                <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-xs text-amber-700 flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span>Anda sedang mengedit akun sendiri. Role dan status akun tidak dapat diubah untuk menjaga keamanan sistem.</span>
                </div>
            @endif

            <!-- Error Validasi Global -->
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" id="editUserForm">
                @csrf
                @method('PUT')

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Masukkan nama lengkap"
                        required
                        maxlength="150"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border @error('name') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        placeholder="contoh@gmail.com"
                        required
                        maxlength="190"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border @error('email') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kata Sandi Baru (Opsional) -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kata Sandi Baru
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Kosongkan jika tidak ingin mengubah password"
                            minlength="8"
                            class="w-full px-4 py-3 pr-12 text-sm bg-slate-50 border @error('password') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        <!-- Tombol Toggle Visibilitas Password -->
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password', 'eyeIcon1')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-0.5"
                            tabindex="-1"
                        >
                            <svg id="eyeIcon1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <input type="hidden" name="password_confirmation" id="password_confirm_mirror">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Sistem -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700 mb-1.5">Role Sistem</label>
                    <div class="relative">
                        <select
                            id="role"
                            name="role"
                            required
                            {{ auth()->id() === $user->id ? 'disabled' : '' }}
                            class="w-full appearance-none px-4 py-3 text-sm bg-slate-50 border @error('role') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition pr-10 {{ auth()->id() === $user->id ? 'opacity-60 cursor-not-allowed' : '' }}"
                        >
                            <option value="intern"  {{ old('role', $user->role) === 'intern'  ? 'selected' : '' }}>Peserta Magang (Intern)</option>
                            <option value="pic"     {{ old('role', $user->role) === 'pic'     ? 'selected' : '' }}>PIC Mentor</option>
                            <option value="admin"   {{ old('role', $user->role) === 'admin'   ? 'selected' : '' }}>Admin / HR</option>
                        </select>
                        <!-- Chevron Icon -->
                        <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                    {{-- Jika disabled, kirim value role lama via hidden input --}}
                    @if(auth()->id() === $user->id)
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif
                </div>

                <!-- Status Akun (hanya tampil jika bukan akun sendiri) -->
                @if(auth()->id() !== $user->id)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Akun</label>
                    <div class="flex items-center gap-4">
                        <!-- Aktif -->
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input
                                type="radio"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 text-[#015987] border-slate-300 focus:ring-[#015987]/30"
                            >
                            <span class="text-sm text-slate-700 group-hover:text-slate-900 transition">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>
                                Aktif
                            </span>
                        </label>
                        <!-- Nonaktif -->
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input
                                type="radio"
                                name="is_active"
                                value="0"
                                {{ old('is_active', $user->is_active ? '1' : '0') === '0' ? 'checked' : '' }}
                                class="w-4 h-4 text-[#015987] border-slate-300 focus:ring-[#015987]/30"
                            >
                            <span class="text-sm text-slate-700 group-hover:text-slate-900 transition">
                                <span class="inline-block w-2 h-2 rounded-full bg-slate-400 mr-1"></span>
                                Nonaktif
                            </span>
                        </label>
                    </div>
                    @error('is_active')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @else
                    {{-- Jika edit akun sendiri, pertahankan status aktif --}}
                    <input type="hidden" name="is_active" value="1">
                @endif

                <!-- Akses Cepat Profil Peserta Magang (Jika Role Intern) -->
                @if($user->isIntern())
                    <div class="p-4 rounded-xl bg-sky-50/80 border border-sky-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Profil Khusus Peserta Magang</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $user->internProfile?->institution ? $user->internProfile->institution . ' • ' . $user->internProfile->formatted_period : 'Data asal kampus dan periode magang belum diatur.' }}
                                </p>
                            </div>
                        </div>
                        <a 
                            href="{{ route('admin.intern-profiles.edit', $user) }}" 
                            class="px-3 py-1.5 rounded-lg bg-[#015987] hover:bg-[#014467] text-white text-xs font-semibold transition flex-shrink-0"
                        >
                            {{ $user->internProfile ? 'Edit Profil' : 'Lengkapi Profil' }} &rarr;
                        </a>
                    </div>
                @endif

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="flex-1 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="flex-1 py-3 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-sm shadow-xs hover:shadow-md transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle visibilitas password
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
        }
    }

    // Mirror password ke password_confirmation agar validasi backend lolos
    document.getElementById('password').addEventListener('input', function () {
        document.getElementById('password_confirm_mirror').value = this.value;
    });
</script>
@endpush
