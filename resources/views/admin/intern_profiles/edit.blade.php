@extends('layouts.app')

@section('title', 'Edit Profil Peserta - ' . $user->name)

@section('page-title', 'Profil Peserta Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional – Pengelolaan Data Akademik & Periode Magang')

@section('content')
<div class="flex items-start justify-center min-h-[70vh]">
    <div class="w-full max-w-lg">

        <!-- Card Form Sesuai Standar WANTRACE -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">

            <!-- Judul & Subjudul -->
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">Edit Profil Peserta</h2>
                <p class="text-sm text-[#015987] font-medium mt-1">Lengkapi data institusi dan periode magang peserta.</p>
            </div>

            <!-- Card Ringkas Info Peserta -->
            <div class="mb-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr(preg_replace('/\s+/', ' ', trim($user->name)), 0, 1)) }}{{ strtoupper(substr(strstr(preg_replace('/\s+/', ' ', trim($user->name)), ' ') ?: ' ', 1, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 leading-tight">{{ $user->name }}</h4>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-sky-100 text-sky-700">
                        INTERN
                    </span>
                    <p class="text-[10px] font-semibold mt-1 {{ $user->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $user->is_active ? '● Aktif' : '○ Nonaktif' }}
                    </p>
                </div>
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

            <!-- Formulir Profil Magang -->
            <form method="POST" action="{{ route('admin.intern-profiles.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- 1. Asal Institusi / Kampus -->
                <div>
                    <label for="institution" class="block text-sm font-semibold text-slate-700 mb-1.5">Asal Institusi / Universitas</label>
                    <input 
                        type="text" 
                        id="institution" 
                        name="institution" 
                        value="{{ old('institution', $profile?->institution) }}"
                        placeholder="Contoh: Politeknik Negeri Padang"
                        maxlength="150"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border @error('institution') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('institution')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 3. Program Studi / Jurusan -->
                <div>
                    <label for="study_program" class="block text-sm font-semibold text-slate-700 mb-1.5">Program Studi / Jurusan</label>
                    <input 
                        type="text" 
                        id="study_program" 
                        name="study_program" 
                        value="{{ old('study_program', $profile?->study_program) }}"
                        placeholder="Contoh: Teknologi Rekayasa Perangkat Lunak"
                        maxlength="150"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border @error('study_program') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('study_program')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 4. Rentang Tanggal Magang (Grid 2 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="internship_start_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="internship_start_date" 
                            name="internship_start_date" 
                            value="{{ old('internship_start_date', $profile?->internship_start_date?->format('Y-m-d')) }}"
                            required
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border @error('internship_start_date') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        @error('internship_start_date')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="internship_end_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Tanggal Selesai
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input 
                            type="date" 
                            id="internship_end_date" 
                            name="internship_end_date" 
                            value="{{ old('internship_end_date', $profile?->internship_end_date?->format('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border @error('internship_end_date') border-rose-300 @else border-slate-200 @enderror rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        @error('internship_end_date')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 5. Indikator Durasi Magang -->
                <div class="px-4 py-3 rounded-xl bg-sky-50/70 border border-sky-100 flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Estimasi Durasi Magang:</span>
                    </span>
                    <span id="durationText" class="font-bold text-[#015987]">
                        {{ $profile && $profile->internship_start_date ? $profile->duration_in_months . ' Bulan' : 'Tentukan rentang tanggal' }}
                    </span>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <a 
                        href="{{ route('admin.intern-profiles.index') }}" 
                        class="flex-1 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center transition"
                    >
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 py-3 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-sm shadow-xs hover:shadow-md transition"
                    >
                        Simpan Profil
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const startDateInput = document.getElementById('internship_start_date');
    const endDateInput   = document.getElementById('internship_end_date');
    const durationText   = document.getElementById('durationText');

    function calculateDuration() {
        const startVal = startDateInput.value;
        const endVal   = endDateInput.value;

        if (!startVal) {
            durationText.textContent = 'Tentukan rentang tanggal';
            durationText.className = 'font-bold text-[#015987]';
            return;
        }

        const startDate = new Date(startVal);
        const endDate   = endVal ? new Date(endVal) : new Date();

        if (endVal && endDate < startDate) {
            durationText.textContent = 'Tanggal selesai harus setelah tanggal mulai';
            durationText.className = 'font-bold text-rose-500';
            return;
        }

        durationText.className = 'font-bold text-[#015987]';

        let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
        months -= startDate.getMonth();
        months += endDate.getMonth();

        if (endDate.getDate() < startDate.getDate()) {
            months--;
        }

        const finalMonths = Math.max(1, Math.round(months <= 0 ? 1 : months));
        const days = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
        durationText.textContent = finalMonths + ' Bulan (' + days + ' Hari)';
    }

    startDateInput.addEventListener('change', calculateDuration);
    endDateInput.addEventListener('change', calculateDuration);

    if (startDateInput.value) {
        calculateDuration();
    }
</script>
@endpush
