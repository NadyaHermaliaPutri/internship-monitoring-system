@extends('layouts.app')

@section('title', 'Tambah Logbook Harian')
@section('page-title', 'Tambah Logbook Harian')
@section('page-subtitle', 'Formulir pencatatan aktivitas harian peserta magang')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-[#015987]">Formulir Aktivitas Harian</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-[#015987] border border-sky-200">
                        WIB
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Anda dapat menambahkan lebih dari 1 aktivitas per hari (maksimal mundur {{ $maxBackdateDays }} hari dari tanggal server Asia/Jakarta).
                </p>
            </div>
            <a href="{{ route('intern.logbooks.index') }}" class="text-xs font-bold text-[#015987] hover:underline">
                &larr; Kembali ke Riwayat
            </a>
        </div>

        <form method="POST" action="{{ route('intern.logbooks.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="log_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="log_date"
                        name="log_date"
                        value="{{ old('log_date', $todayDate) }}"
                        min="{{ $earliestDate }}"
                        max="{{ $todayDate }}"
                        required
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('log_date') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">
                        Rentang diizinkan: {{ \Carbon\Carbon::parse($earliestDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($todayDate)->translatedFormat('d M Y') }} (WIB)
                    </p>
                    @error('log_date')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="related_task_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Terkait Tugas PIC <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <select
                        id="related_task_id"
                        name="related_task_id"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('related_task_id') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                    >
                        <option value="">Kegiatan Mandiri / Umum (Tidak terkait tugas tertentu)</option>
                        @foreach ($taskOptions as $taskOption)
                            <option value="{{ $taskOption->id }}" @selected((string) old('related_task_id') === (string) $taskOption->id)>
                                {{ $taskOption->title }} — PIC: {{ $taskOption->pic?->name ?? 'PIC' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Jika dipilih, logbook ini hanya ditinjau oleh PIC pemberi tugas tersebut.
                    </p>
                    @error('related_task_id')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Judul Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    maxlength="150"
                    required
                    placeholder="Contoh: Implementasi validasi form presensi harian"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                >
                @error('title')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="activity_description" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Uraian Aktivitas <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="activity_description"
                    name="activity_description"
                    rows="5"
                    required
                    placeholder="Jelaskan secara rinci kegiatan yang Anda kerjakan (minimal 10 karakter)..."
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('activity_description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                >{{ old('activity_description') }}</textarea>
                @error('activity_description')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="result" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Hasil / Output Kegiatan <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea
                    id="result"
                    name="result"
                    rows="3"
                    placeholder="Contoh: PR #12 siap ditinjau / modul selesai diuji lokal"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('result') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                >{{ old('result') }}</textarea>
                @error('result')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <a href="{{ route('intern.logbooks.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold shadow-xs transition">
                    Simpan Logbook
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
