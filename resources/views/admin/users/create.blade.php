@extends('layouts.app')

@section('title', 'Tambah Akun Baru')

@section('page-title', 'Kelola Akun Pengguna')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengelolaan Hak Akses & Penugasan PIC')

@section('content')
<div class="flex items-start justify-center min-h-[70vh]">
    <div class="w-full max-w-lg">

        <!-- Card Form Sesuai Mockup -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">

            <!-- Judul & Subjudul -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">Tambah Akun Baru</h2>
                <p class="text-sm text-[#015987] font-medium mt-1">Lengkapi informasi berikut untuk menambah pengguna baru ke sistem.</p>
            </div>

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

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" id="createUserForm">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
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
                        value="{{ old('email') }}"
                        placeholder="contoh@gmail.com"
                        required
                        maxlength="190"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border @error('email') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kata Sandi dengan Toggle Mata -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="xxxxxxxx"
                            required
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
                            <!-- Eye-off (default: password disembunyikan) -->
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
                            class="w-full appearance-none px-4 py-3 text-sm bg-slate-50 border @error('role') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition pr-10"
                        >
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih Role</option>
                            <option value="intern" {{ old('role') === 'intern' ? 'selected' : '' }}>Peserta Magang (Intern)</option>
                            <option value="pic" {{ old('role') === 'pic' ? 'selected' : '' }}>PIC Mentor</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin / HR</option>
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
                </div>

                <!-- Input tersembunyi untuk is_active (default: aktif) -->
                <input type="hidden" name="is_active" value="1">

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
                        Tambah Akun
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
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            // Ganti ke ikon "eye" (terlihat)
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        } else {
            input.type = 'password';
            // Kembali ke ikon "eye-off"
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
        }
    }

    // Mirror password ke password_confirmation agar validasi backend lolos
    document.getElementById('password').addEventListener('input', function() {
        document.getElementById('password_confirm_mirror').value = this.value;
    });
</script>
@endpush
