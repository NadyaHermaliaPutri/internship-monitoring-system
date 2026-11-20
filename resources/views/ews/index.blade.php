@extends('layouts.app')

@section('title', 'Early Warning System (EWS)')
@section('page-title', 'Early Warning System (EWS)')
@section('page-subtitle', $isPicView ? 'Pemantauan Kepatuhan Peserta Magang Bimbingan' : 'Pemantauan Indikator Kepatuhan Seluruh Peserta Magang (Global)')

@section('page-badge')
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        3 Indikator Resmi
    </span>
    @if(!$isWorkingDay)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Hari Libur Operasional
        </span>
    @elseif($date === $today)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
            Hari Kerja Aktif (Hari Ini)
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200 shadow-2xs">
            Riwayat Tanggal Lampau
        </span>
    @endif
@endsection

@section('header-actions')
    @if(!$isPicView)
        <button 
            type="button" 
            onclick="document.getElementById('ews-settings-modal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200/80 shadow-xs transition cursor-pointer"
        >
            <svg class="w-4 h-4 text-[#015987]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Atur Jam Cutoff</span>
        </button>
    @endif
@endsection

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER ATURAN BISNIS & INFORMASI INTEGRITAS EWS -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0 text-amber-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-slate-800">Prinsip & Pengecualian Resmi Early Warning System</h4>
                <p class="text-[11px] sm:text-xs text-slate-600 mt-1 leading-relaxed">
                    EWS adalah alat bantu pemantauan internal untuk tinjauan manusia. Hari libur resmi/akhir pekan dikecualikan otomatis (kecuali ditandai hari kerja override di Kalender), izin/sakit yang telah disetujui (approved) dikecualikan otomatis, dan hanya mengevaluasi akun peserta aktif. EWS <strong>tidak pernah</strong> membuat catatan alpa otomatis ke presensi.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-100 flex-shrink-0">
            <div class="text-right w-full md:w-auto">
                <span class="text-[10px] uppercase font-bold text-slate-600 tracking-wider block">Jam Cutoff Aktif</span>
                <div class="flex items-center justify-end gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-[#015987] border border-sky-200" title="Batas Cutoff EWS Presensi Masuk">
                        Presensi: {{ $summary['cutoff_attendance'] }} WIB
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Batas Cutoff EWS Pengisian Logbook">
                        Logbook: {{ $summary['cutoff_logbook'] }} WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. FILTER TANGGAL & STATUS HARI -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <label for="date-picker" class="text-xs font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#015987]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Pilih Tanggal Evaluasi:</span>
                </label>
                <div class="flex items-center gap-2">
                    <input 
                        type="date" 
                        id="date-picker"
                        name="date" 
                        value="{{ $date }}"
                        class="px-3 py-2 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                    >
                    <button 
                        type="submit" 
                        class="px-3.5 py-2 text-xs font-bold bg-[#015987] hover:bg-[#014467] text-white rounded-xl shadow-xs transition cursor-pointer"
                    >
                        Tampilkan
                    </button>
                    @if($date !== $today)
                        <a 
                            href="{{ url()->current() }}?type={{ $type }}" 
                            class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition"
                        >
                            Reset ke Hari Ini
                        </a>
                    @endif
                </div>
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Mengevaluasi data tanggal: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
            </div>
        </form>
    </div>

    <!-- 3. KARTU RINGKASAN METRIK (3 INDIKATOR RESMI EWS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kartu 0: Total Sinyal -->
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=all" 
            class="p-4 sm:p-5 rounded-2xl bg-white border {{ $type === 'all' ? 'border-[#015987] ring-2 ring-[#015987]/15' : 'border-slate-200/80 hover:border-slate-300' }} shadow-xs transition group cursor-pointer block"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sinyal EWS</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs group-hover:bg-[#015987] group-hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $summary['total_warning_count'] > 0 ? 'text-slate-900' : 'text-emerald-600' }}">
                    {{ $summary['total_warning_count'] }}
                </span>
                <span class="text-xs text-slate-400">peringatan</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Akumulasi seluruh peringatan aktif</p>
        </a>

        <!-- Kartu 1: Belum Presensi -->
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=attendance" 
            class="p-4 sm:p-5 rounded-2xl bg-white border {{ $type === 'attendance' ? 'border-rose-500 ring-2 ring-rose-500/15' : 'border-slate-200/80 hover:border-slate-300' }} shadow-xs transition group cursor-pointer block"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Belum Presensi</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs group-hover:bg-rose-600 group-hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $summary['unsubmitted_attendance_count'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $summary['unsubmitted_attendance_count'] }}
                </span>
                <span class="text-xs text-slate-400">peserta</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Setelah cutoff {{ $summary['cutoff_attendance'] }} WIB</p>
        </a>

        <!-- Kartu 2: Logbook Kosong -->
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=logbook" 
            class="p-4 sm:p-5 rounded-2xl bg-white border {{ $type === 'logbook' ? 'border-amber-500 ring-2 ring-amber-500/15' : 'border-slate-200/80 hover:border-slate-300' }} shadow-xs transition group cursor-pointer block"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Logbook Kosong</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $summary['missing_logbook_count'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $summary['missing_logbook_count'] }}
                </span>
                <span class="text-xs text-slate-400">peserta</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Khusus manual, cutoff {{ $summary['cutoff_logbook'] }} WIB</p>
        </a>

        <!-- Kartu 3: Tugas Overdue -->
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=task" 
            class="p-4 sm:p-5 rounded-2xl bg-white border {{ $type === 'task' ? 'border-red-600 ring-2 ring-red-600/15' : 'border-slate-200/80 hover:border-slate-300' }} shadow-xs transition group cursor-pointer block"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-red-600 uppercase tracking-wider">Tugas Overdue</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold text-xs group-hover:bg-red-600 group-hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $summary['overdue_tasks_count'] > 0 ? 'text-red-600' : 'text-slate-900' }}">
                    {{ $summary['overdue_tasks_count'] }}
                </span>
                <span class="text-xs text-slate-400">tugas</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Melewati deadline terbaru & belum accepted</p>
        </a>
    </div>

    <!-- 4. TAB NAVIGASI KATEGORI SINYAL -->
    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 overflow-x-auto">
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=all" 
            class="px-4 py-2 text-xs font-bold rounded-xl transition whitespace-nowrap {{ $type === 'all' ? 'bg-[#015987] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}"
        >
            Semua ({{ $summary['total_warning_count'] }})
        </a>
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=attendance" 
            class="px-4 py-2 text-xs font-bold rounded-xl transition whitespace-nowrap {{ $type === 'attendance' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}"
        >
            Belum Presensi ({{ $summary['unsubmitted_attendance_count'] }})
        </a>
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=logbook" 
            class="px-4 py-2 text-xs font-bold rounded-xl transition whitespace-nowrap {{ $type === 'logbook' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}"
        >
            Logbook Belum Diisi ({{ $summary['missing_logbook_count'] }})
        </a>
        <a 
            href="{{ url()->current() }}?date={{ $date }}&type=task" 
            class="px-4 py-2 text-xs font-bold rounded-xl transition whitespace-nowrap {{ $type === 'task' ? 'bg-red-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white' }}"
        >
            Tugas Overdue ({{ $summary['overdue_tasks_count'] }})
        </a>
    </div>

    <!-- 5. DAFTAR PERINGATAN EWS AKTIF -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-bold text-sm sm:text-base text-slate-900">
                    Daftar Sinyal Early Warning Terdeteksi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan {{ $warnings->count() }} sinyal berdasarkan kriteria filter aktif
                </p>
            </div>
            <div class="text-xs text-slate-400">
                Waktu server: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
            </div>
        </div>

        @if($warnings->isEmpty())
            <!-- Empty State Bersih -->
            <div class="p-10 sm:p-14 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-200 mx-auto flex items-center justify-center text-emerald-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h4 class="text-base sm:text-lg font-bold text-slate-800">Semua Terkendali</h4>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
                    @if(!$isWorkingDay)
                        Hari ini adalah hari libur operasional atau akhir pekan. Tidak ada kewajiban presensi maupun pengisian logbook harian.
                    @elseif($date === $today && \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') < $summary['cutoff_attendance'])
                        Belum memasuki jam cutoff EWS presensi ({{ $summary['cutoff_attendance'] }} WIB). Sinyal belum terpicu.
                    @else
                        Tidak ada peserta yang melanggar batas cutoff presensi, batas cutoff logbook manual, maupun tugas overdue untuk kategori yang dipilih.
                    @endif
                </p>
            </div>
        @else
            <!-- Tabel Responsif Sinyal EWS -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-4 sm:px-6">Peserta Magang</th>
                            <th class="py-3 px-4">Indikator EWS</th>
                            <th class="py-3 px-4">Keterangan / Detail Peringatan</th>
                            <th class="py-3 px-4">Tanggal / Waktu</th>
                            <th class="py-3 px-4 text-right">Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($warnings as $warning)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Peserta -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-xs text-[#015987] flex-shrink-0">
                                            {{ strtoupper(substr($warning['intern']?->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 block truncate">
                                                {{ $warning['intern']?->name ?? 'Peserta' }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 block truncate">
                                                {{ $warning['intern']?->email ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Indikator Badge -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($warning['type'] === 'unsubmitted_attendance')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ $warning['badge'] }}
                                        </span>
                                    @elseif($warning['type'] === 'missing_logbook')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $warning['badge'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            {{ $warning['badge'] }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Detail Peringatan -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-800 block">
                                        {{ $warning['title'] }}
                                    </span>
                                    <span class="text-slate-500 text-[11px] mt-0.5 block leading-relaxed">
                                        {{ $warning['detail'] }}
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                    @if(isset($warning['deadline_at']) && $warning['deadline_at'])
                                        <span class="text-red-600 font-semibold block">
                                            {{ $warning['deadline_at']->translatedFormat('d M Y, H:i') }} WIB
                                        </span>
                                    @else
                                        {{ \Carbon\Carbon::parse($warning['date'] ?? $date)->translatedFormat('d M Y') }}
                                    @endif
                                </td>

                                <!-- Tindak Lanjut -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @php $rolePrefix = auth()->user()->role ?? 'admin'; @endphp
                                    @if($warning['type'] === 'unsubmitted_attendance')
                                        <a 
                                            href="{{ $isPicView ? route('pic.attendance.index') : route('admin.attendance.index') }}" 
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#015987] hover:text-[#014467] hover:underline"
                                        >
                                            <span>Lihat Presensi</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @elseif($warning['type'] === 'missing_logbook')
                                        <a 
                                            href="{{ route($rolePrefix . '.logbooks.index') }}" 
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#015987] hover:text-[#014467] hover:underline"
                                        >
                                            <span>Lihat Logbook</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @elseif($warning['type'] === 'overdue_task' && isset($warning['task']))
                                        <a 
                                            href="{{ route($rolePrefix . '.tasks.show', $warning['task']->id) }}" 
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-800 hover:underline"
                                        >
                                            <span>Tinjau Tugas</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<!-- 6. MODAL PENGATURAN JAM CUTOFF EWS (HANYA UNTUK ROLE ADMIN / HR) -->
@if(!$isPicView)
<div 
    id="ews-settings-modal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden"
>
    <div class="relative w-full max-w-md bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-8">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#015987] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900">Atur Jam Cutoff EWS</h3>
                    <p class="text-xs text-slate-500">Konfigurasi batas alarm kepatuhan harian</p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="document.getElementById('ews-settings-modal').classList.add('hidden')"
                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form Edit Cutoff -->
        <form method="POST" action="{{ route('admin.ews.settings.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <!-- Cutoff Presensi -->
            <div>
                <label for="attendance_cutoff_time" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Jam Cutoff Presensi Masuk (WIB)
                </label>
                <div class="relative">
                    <input 
                        type="time" 
                        id="attendance_cutoff_time"
                        name="attendance_cutoff_time" 
                        value="{{ old('attendance_cutoff_time', $settings->attendance_cutoff_time) }}"
                        required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                    >
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    Default: <strong>09:00 WIB</strong>. Catatan: Pukul 08:05 WIB adalah batas toleransi keterlambatan presensi masuk (on_time vs late). Jam cutoff EWS di atas adalah batas waktu untuk memicu sinyal peringatan bahwa peserta belum presensi sama sekali.
                </p>
                @error('attendance_cutoff_time')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cutoff Logbook -->
            <div>
                <label for="logbook_cutoff_time" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Jam Cutoff Pengisian Logbook (WIB)
                </label>
                <div class="relative">
                    <input 
                        type="time" 
                        id="logbook_cutoff_time"
                        name="logbook_cutoff_time" 
                        value="{{ old('logbook_cutoff_time', $settings->logbook_cutoff_time) }}"
                        required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                    >
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    Default: <strong>17:00 WIB</strong>. Jam cutoff ini mengevaluasi apakah peserta sudah mencatat logbook mandiri (source = manual) sebelum jam kerja berakhir.
                </p>
                @error('logbook_cutoff_time')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Audit Trail -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] text-slate-500 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Setiap perubahan jam cutoff akan dicatat otomatis ke riwayat audit trail (audit_logs).</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button 
                    type="button" 
                    onclick="document.getElementById('ews-settings-modal').classList.add('hidden')"
                    class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 text-xs font-bold text-white bg-[#015987] hover:bg-[#014467] rounded-xl shadow-xs transition cursor-pointer"
                >
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
