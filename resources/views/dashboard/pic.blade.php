@extends('layouts.app')

@section('title', 'Dashboard Bimbingan PIC')
@section('page-title', 'Dashboard Bimbingan PIC')

@section('page-badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-[#015987] border border-sky-200">
        Model Penugasan Bersama (Multi-PIC)
    </span>
@endsection

@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengawasan Teknis & Progres Pekerjaan Proyek')
@section('search-placeholder', 'Cari anak bimbingan...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PIC (TOP 4 STAT CARDS 100% DINAMIS) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Tugas Aktif Anda -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Tugas Aktif Anda</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $taskStats['active_tasks'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <div class="flex items-center justify-between mt-1.5 text-[11px]">
                    <span class="font-semibold text-sky-600 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        Dikerjakan {{ $taskStats['assigned_interns_count'] }} peserta
                    </span>
                    <a href="{{ route('pic.tasks.index') }}" class="text-[#015987] font-semibold hover:underline">
                        Buka &rsaquo;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Mendekati Tenggat (< 24 Jam) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Mendekati Tenggat</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold {{ $taskStats['approaching_deadline'] > 0 ? 'text-amber-600' : 'text-[#015987]' }}">{{ $taskStats['approaching_deadline'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold {{ $taskStats['approaching_deadline'] > 0 ? 'text-amber-600' : 'text-slate-400' }} mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $taskStats['approaching_deadline'] > 0 ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                    Batas waktu &lt; 24 Jam
                </p>
            </div>
        </div>

        <!-- Card 3: Butuh Review Anda -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Butuh Review Anda</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold {{ $taskStats['awaiting_review'] > 0 ? 'text-emerald-600' : 'text-[#015987]' }}">{{ $taskStats['awaiting_review'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Kiriman</span>
                </div>
                <div class="flex items-center justify-between mt-1.5 text-[11px]">
                    <span class="font-semibold {{ $taskStats['awaiting_review'] > 0 ? 'text-emerald-600' : 'text-slate-400' }} flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full {{ $taskStats['awaiting_review'] > 0 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        Khusus tugas Anda
                    </span>
                    <a href="{{ route('pic.tasks.index', ['status' => 'submitted']) }}" class="text-[#015987] font-semibold hover:underline">
                        Periksa &rsaquo;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4: Dalam Proses Revisi -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Dalam Proses Revisi</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold {{ $taskStats['in_revision'] > 0 ? 'text-rose-600' : 'text-[#015987]' }}">{{ $taskStats['in_revision'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold {{ $taskStats['in_revision'] > 0 ? 'text-rose-500' : 'text-slate-400' }} mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $taskStats['in_revision'] > 0 ? 'bg-rose-500' : 'bg-slate-300' }}"></span>
                    Menunggu intern kirim ulang
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM (KONTEN UTAMA PIC) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ========================================================== -->
        <!-- KOLOM KIRI (LEBAR 7/12): PRESENSI BIMBINGAN, SUBMISI, & TUGAS -->
        <!-- ========================================================== -->
        <div class="lg:col-span-7 space-y-8">

            <!-- WIDGET MODUL 2: PRESENSI & IZIN ANAK BIMBINGAN HARI INI (READ-ONLY PIC) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#015987]">Presensi &amp; Izin Anak Bimbingan Hari Ini</h3>
                            <span class="text-[10px] font-semibold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full">
                                Read-Only PIC
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Status kehadiran real-time khusus peserta aktif dalam bimbingan Anda ({{ $mentoringSummary['total_assigned_interns'] }} peserta)
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('pic.attendance.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Riwayat Presensi &rsaquo;
                        </a>
                        <a href="{{ route('pic.leave.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Riwayat Izin &rsaquo;
                        </a>
                    </div>
                </div>

                @if(! ($mentoringSummary['day_status']['is_working_day'] ?? true))
                    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 text-xs text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Hari ini adalah <strong>{{ $mentoringSummary['day_status']['label'] ?? 'Hari Libur' }}</strong>. Tidak ada kewajiban presensi regular.</span>
                    </div>
                @endif

                <div class="p-6">
                    @if($mentoringSummary['total_assigned_interns'] === 0)
                        <div class="text-center py-6 text-xs text-slate-400">
                            Anda belum memiliki peserta magang aktif yang ditugaskan ke akun Anda saat ini.
                        </div>
                    @else
                        <!-- Ringkasan Cepat -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total Bimbingan</span>
                                <span class="text-lg font-bold text-slate-800">{{ $mentoringSummary['total_assigned_interns'] }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100">
                                <span class="text-[10px] text-emerald-600 block font-semibold uppercase">Sudah Hadir</span>
                                <span class="text-lg font-bold text-emerald-700">{{ $mentoringSummary['total_present'] }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100">
                                <span class="text-[10px] text-amber-600 block font-semibold uppercase">Izin / Sakit Sah</span>
                                <span class="text-lg font-bold text-amber-700">{{ $mentoringSummary['approved_leaves']->count() }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50/50 border border-rose-100">
                                <span class="text-[10px] text-rose-600 block font-semibold uppercase">Belum Check-In</span>
                                <span class="text-lg font-bold text-rose-700">
                                    {{ max(0, $mentoringSummary['total_assigned_interns'] - $mentoringSummary['total_present'] - $mentoringSummary['approved_leaves']->count()) }}
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Peserta Bimbingan Hari Ini -->
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @foreach($mentoringSummary['assigned_interns'] as $intern)
                                @php
                                    $att = $mentoringSummary['attendances']->firstWhere('intern_user_id', $intern->id);
                                    $leave = $mentoringSummary['approved_leaves']->firstWhere('intern_user_id', $intern->id);
                                @endphp
                                <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-100 text-[#015987] font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($intern->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-[#015987] leading-tight">{{ $intern->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $intern->email }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($att)
                                            @if($att->isRejected())
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                    Ditolak HR (Perlu Foto Ulang)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $att->isOnTime() ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $att->isOnTime() ? 'bg-teal-500' : 'bg-rose-500' }}"></span>
                                                    {{ $att->isOnTime() ? 'Hadir Tepat Waktu' : 'Terlambat' }} ({{ \Carbon\Carbon::parse($att->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB)
                                                </span>
                                            @endif
                                            @if($att->photo_media_id)
                                                <a href="{{ route('media.private.show', $att->photo_media_id) }}" target="_blank" class="p-1 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800 text-[10px] font-medium" title="Lihat Foto Bukti">
                                                    Foto Bukti
                                                </a>
                                            @endif
                                        @elseif($leave)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ $leave->isSick() ? 'Sakit Resmi' : 'Izin Resmi' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Belum Check-In
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- KARTU 1: SUBMISI MENUNGGU REVIEW ANDA (DINAMIS DARI DATABASE) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Submisi Menunggu Review Anda</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tugas yang telah dikumpulkan dan siap diuji/ditinjau</p>
                    </div>
                    <span class="text-[11px] font-semibold {{ $awaitingReviewTasks->count() > 0 ? 'text-emerald-700 bg-emerald-50 border border-emerald-200' : 'text-slate-500 bg-slate-100' }} px-3 py-1 rounded-full">
                        {{ $awaitingReviewTasks->count() }} Menunggu Keputusan
                    </span>
                </div>

                <!-- Daftar Submisi -->
                <div class="p-6 space-y-4">
                    @forelse($awaitingReviewTasks as $task)
                        <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-[#015987] text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($task->intern->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <span class="text-xs font-bold text-[#015987]">{{ $task->intern->name ?? 'Peserta' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $task->status?->value === 'resubmitted' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-sky-50 text-sky-600 border border-sky-200' }}">
                                        {{ $task->status?->label() }}
                                    </span>
                                    @if($task->intern->internProfile?->institution_name)
                                        <span class="text-[11px] text-slate-400">
                                            {{ $task->intern->internProfile->institution_name }}
                                        </span>
                                    @endif
                                </div>

                                <h4 class="text-xs font-bold text-[#015987]">
                                    {{ $task->title }}
                                </h4>

                                <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                    <span>Tenggat: {{ $task->deadline_at?->translatedFormat('d M Y, H:i') ?? 'Tanpa deadline' }} WIB</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-400">Update: {{ $task->updated_at?->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex-shrink-0">
                                <a href="{{ route('pic.tasks.show', $task) }}" class="px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-xs inline-block">
                                    Periksa &amp; Review
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-slate-400 space-y-2">
                            <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Tidak ada tugas bimbingan Anda yang sedang menunggu review saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KARTU 2: PESERTA DENGAN TUGAS DARI ANDA (POOL BERSAMA) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#015987]">Peserta dengan Tugas dari Anda</h3>
                            <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                Pool Bersama
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Menampilkan peserta aktif yang sedang memegang tugas buatan Anda dan beban kerja lainnya</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('pic.tasks.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Semua Tugas ({{ $taskStats['active_tasks'] }})
                        </a>
                        <a href="{{ route('pic.tasks.create') }}" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#015987] hover:bg-[#01486e] rounded-xl transition flex items-center gap-1 shadow-xs">
                            <span>+</span>
                            <span>Buat Tugas</span>
                        </a>
                    </div>
                </div>

                <!-- Tabel Data Peserta & Tugas -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-6">Peserta Magang</th>
                                <th class="py-4 px-5">Tugas Anda (Status)</th>
                                <th class="py-4 px-5">PIC Lain yang Terlibat</th>
                                <th class="py-4 px-5">Beban Kerja</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($internsWithTasks as $row)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-6">
                                        <div>
                                            <p class="font-bold text-[#015987] text-xs leading-none">{{ $row['intern']->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1">{{ $row['intern']->internProfile?->institution_name ?? $row['intern']->email }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="space-y-1">
                                            <p class="font-medium text-[#015987] text-xs">{{ $row['latest_task']->title }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $row['latest_task']->status?->value === 'accepted' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }}">
                                                {{ strtoupper($row['latest_task']->status?->value ?? 'ASSIGNED') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        @if($row['other_pics']->isNotEmpty())
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @foreach($row['other_pics'] as $otherPic)
                                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-md">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                        {{ $otherPic->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs italic text-slate-400">Tidak ada (Hanya Anda)</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $row['total_active_tasks'] > 2 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            {{ $row['total_active_tasks'] }} Tugas Aktif
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('pic.tasks.show', $row['latest_task']) }}" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition inline-block">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                        Belum ada peserta aktif yang sedang memegang tugas buatan Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- KOLOM KANAN (LEBAR 5/12): EWS & TENGGAT TUGAS PIC          -->
        <!-- ========================================================== -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- KARTU 1: SINYAL PERINGATAN (EWS 3 INDIKATOR RESMI KHUSUS PIC) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#015987]">Sinyal Peringatan (EWS)</h3>
                            <p class="text-[11px] text-slate-400">Peringatan keterlambatan bimbingan Anda</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $ewsSummary['total_warning_count'] > 0 ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                        {{ $ewsSummary['total_warning_count'] }} Sinyal
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($ewsSummary['warnings_list'] as $warning)
                        <div class="p-4 rounded-xl border {{ $warning['level'] === 'danger' ? 'border-rose-200 bg-rose-50/40' : 'border-amber-200 bg-amber-50/40' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $warning['level'] === 'danger' ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                                    {{ $warning['badge'] }}
                                </span>
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $warning['intern']->name ?? 'Peserta' }}
                                </span>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-[#015987]">
                                    {{ $warning['title'] }}
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                    {{ $warning['detail'] }}
                                </p>
                            </div>

                            @if(isset($warning['task']))
                                <div class="pt-2 border-t border-rose-100 flex items-center justify-between text-xs">
                                    <span class="text-[11px] text-slate-400">Tindakan:</span>
                                    <a href="{{ route('pic.tasks.show', $warning['task']) }}" class="font-bold text-[#015987] hover:underline text-xs">
                                        Periksa Tugas &rsaquo;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400 space-y-2">
                            <svg class="w-8 h-8 mx-auto text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-semibold text-emerald-600">Semua anak bimbingan dalam status aman</p>
                            <p class="text-[11px] text-slate-400">Tidak ada presensi terlewat, logbook bolong, atau tugas overdue.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KARTU 2: TENGGAT TUGAS BUATAN ANDA (DINAMIS DARI DATABASE) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-[#015987]">
                        Tenggat Tugas Mendatang
                    </h3>
                    <a href="{{ route('pic.tasks.index') }}" class="text-xs text-[#015987] font-semibold hover:underline">
                        Semua &rsaquo;
                    </a>
                </div>

                <div class="space-y-3.5">
                    @forelse($upcomingDeadlines as $task)
                        @php
                            $now = \Carbon\Carbon::now(config('wantrace.timezone', 'Asia/Jakarta'));
                            $isOverdue = $task->deadline_at->lt($now);
                            $diffHours = (int) $now->diffInHours($task->deadline_at, false);
                        @endphp
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition">
                            <div class="min-w-0 pr-2">
                                <a href="{{ route('pic.tasks.show', $task) }}" class="text-xs font-bold text-[#015987] hover:underline block truncate">
                                    {{ $task->title }}
                                </a>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                    {{ $task->intern->name ?? 'Peserta' }} &bull; {{ $task->deadline_at->translatedFormat('d M, H:i') }} WIB
                                </p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg flex-shrink-0 {{ $isOverdue ? 'bg-rose-50 text-rose-600 border border-rose-200' : ($diffHours <= 24 ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                @if($isOverdue)
                                    Lewat
                                @elseif($diffHours <= 24)
                                    &lt; 24 Jam
                                @else
                                    {{ (int) ceil($diffHours / 24) }} Hari
                                @endif
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">
                            Tidak ada tugas aktif dengan tenggat waktu mendatang.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
