@extends('layouts.app')

@section('title', 'Presensi Harian Peserta Magang')
@section('page-title', 'Presensi Harian Peserta Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Presensi Harian & Riwayat Kehadiran')
@section('search-placeholder', 'Cari tanggal atau riwayat...')

@section('content')
<div class="space-y-8">

    <!-- Flash Alert Sukses / Error -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1 shadow-xs">
            <div class="flex items-center gap-2 text-rose-700 font-bold">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') ?? 'Terjadi kesalahan saat memproses presensi:' }}</span>
            </div>
            @if($errors->any())
                <ul class="list-disc list-inside pl-6 text-rose-600 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Banner Peringatan Koneksi Offline PWA (Aplikasi Membutuhkan Jaringan Aktif) -->
    <div id="offlineNotice" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-3 shadow-xs">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-1.414-1.414m-2.828-2.828A9 9 0 015.636 5.636m0 0L3 3m5.464 5.464a5 5 0 011.414-1.414m2.828 2.828a5 5 0 011.415 1.414"/>
        </svg>
        <div>
            <p class="font-bold">Koneksi Internet Terputus</p>
            <p class="text-[11px] text-amber-700 font-medium mt-0.5">Sistem monitoring magang memerlukan koneksi jaringan internet yang aktif. Transaksi presensi tidak dapat diproses saat perangkat luring (offline).</p>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PRESENSI PESERTA                  -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Status Presensi Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Presensi Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                @if($todayAttendance)
                    @if($todayAttendance->isRejected())
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl sm:text-2xl font-extrabold text-rose-600">Ditolak HR</span>
                        </div>
                        <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Perlu Foto Ulang
                        </p>
                    @else
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-extrabold text-[#015987]">Hadir</span>
                            <span class="text-xs text-slate-500 font-medium">{{ $todayAttendance->checked_in_at->format('H:i') }} WIB</span>
                        </div>
                        <p class="text-[11px] font-semibold {{ $todayAttendance->isOnTime() ? 'text-emerald-600' : 'text-rose-500' }} mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $todayAttendance->isOnTime() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $todayAttendance->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}
                        </p>
                    @endif
                @elseif($approvedLeaveToday)
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl font-extrabold text-sky-700 uppercase">{{ $approvedLeaveToday->leave_type === 'sick' ? 'Sakit' : 'Izin' }}</span>
                    </div>
                    <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        Disetujui HR
                    </p>
                @elseif(! $isWorkingDay)
                    <div class="flex items-baseline gap-2">
                        <span class="text-lg font-bold text-slate-600">Hari Libur</span>
                    </div>
                    <p class="text-[11px] font-medium text-slate-400 mt-1.5">
                        {{ $dayStatus['reason'] }}
                    </p>
                @else
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-rose-500">Belum Presensi</span>
                    </div>
                    <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Batas tepat waktu 08:05 WIB
                    </p>
                @endif
            </div>
        </div>

        <!-- Card 2: Tingkat Kehadiran Bulan Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tingkat Kehadiran</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $monthlyRecap['attendance_rate'] }}%</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    {{ $monthlyRecap['total_present'] }} dari {{ $monthlyRecap['total_working_days'] }} hari kerja bulan {{ Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F') }}
                </p>
            </div>
        </div>

        <!-- Card 3: Tepat Waktu -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tepat Waktu</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $monthlyRecap['on_time_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Hari</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Check-in sebelum 08:05:59 WIB
                </p>
            </div>
        </div>

        <!-- Card 4: Terlambat / Izin -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Terlambat &amp; Izin</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $monthlyRecap['late_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Terlambat</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-500 mt-1.5 flex items-center gap-1">
                    <span>{{ $monthlyRecap['sick_days_count'] }} Sakit • {{ $monthlyRecap['leave_days_count'] }} Izin</span>
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. KONTEN UTAMA: KAMERA SELFIE (ATAS) & RIWAYAT (BAWAH)        -->
    <!-- ============================================================== -->
    <div class="space-y-8">
        
        <!-- ========================================================== -->
        <!-- AREA KAMERA SELFIE (FULL WIDTH)                            -->
        <!-- ========================================================== -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                
                <!-- Header Card Kamera -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-[#015987]">Kamera Pengambilan Selfie Presensi</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Posisikan wajah Anda agar terlihat jelas di layar.</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Indikator Waktu Server Realtime WIB -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-sky-50 text-[#015987] text-xs font-bold border border-sky-200/70">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span id="liveClockWIB">{{ $now->format('H:i:s') }}</span> WIB
                        </span>
                    </div>
                </div>

                {{-- KONDISI 1: SUDAH CHECK-IN DAN TIDAK DITOLAK --}}
                @if($todayAttendance && ! $todayAttendance->isRejected())
                    <div class="bg-[#014467] text-white p-6 sm:p-7 rounded-2xl shadow-xs space-y-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm sm:text-base font-bold text-white">Presensi Masuk Berhasil Tercatat</h4>
                                <p class="text-xs text-sky-100 mt-0.5">Foto selfie berhasil disimpan dengan aman.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="p-3.5 rounded-xl bg-white/10 border border-white/10 space-y-1">
                                <span class="text-[10px] text-sky-200 uppercase font-semibold">Waktu Tercatat</span>
                                <p class="text-lg font-extrabold text-white">{{ $todayAttendance->checked_in_at->format('H:i:s') }} WIB</p>
                                <span class="text-[11px] {{ $todayAttendance->isOnTime() ? 'text-emerald-300' : 'text-rose-300' }} font-bold block">
                                    Status: {{ $todayAttendance->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white/10 border border-white/10 flex items-center gap-3">
                                <div class="w-14 h-14 rounded-lg bg-slate-800 overflow-hidden flex-shrink-0 border border-white/20">
                                    <img src="{{ route('media.private.show', $todayAttendance->photo_media_id) }}" alt="Foto Selfie" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] text-sky-200 uppercase font-semibold">Bukti Selfie</span>
                                    <p class="text-xs text-white font-medium truncate">{{ $todayAttendance->photo->original_name }}</p>
                                    <a href="{{ route('media.private.show', $todayAttendance->photo_media_id) }}" target="_blank" class="text-[11px] text-sky-200 hover:text-white font-semibold underline mt-1 inline-block">
                                        Lihat Resolusi Penuh &rsaquo;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <p class="text-[11px] text-sky-200 text-center pt-2 border-t border-white/10">
                            Anda telah menyelesaikan presensi masuk hari ini. Tidak ada presensi checkout.
                        </p>
                    </div>

                {{-- KONDISI 2: HARI INI BUKAN HARI KERJA --}}
                @elseif(! $isWorkingDay)
                    <div class="p-8 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">{{ $dayStatus['label'] }}</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Hari ini adalah {{ $dayStatus['reason'] }}. Presensi masuk harian tidak diperlukan pada hari libur operasional kantor.
                        </p>
                    </div>

                {{-- KONDISI 3: ADA IZIN/SAKIT YANG SUDAH DISETUJUI (HANYA JIKA TIDAK DITOLAK HR HARI INI) --}}
                @elseif($approvedLeaveToday && ! ($todayAttendance && $todayAttendance->isRejected()))
                    <div class="p-8 rounded-2xl bg-sky-50/60 border border-sky-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-sky-100 text-[#015987] mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#015987] text-white uppercase">
                            {{ $approvedLeaveToday->leave_type === 'sick' ? 'Permohonan Sakit' : 'Permohonan Izin' }}
                        </span>
                        <h4 class="text-sm font-bold text-slate-800">Izin Anda Telah Disetujui HR</h4>
                        <p class="text-xs text-slate-600 max-w-md mx-auto">
                            "{{ $approvedLeaveToday->reason }}"<br>
                            Rentang: {{ $approvedLeaveToday->start_date->translatedFormat('d F Y') }} s.d. {{ $approvedLeaveToday->end_date->translatedFormat('d F Y') }}.
                        </p>
                    </div>

                {{-- KONDISI 4: BISA MELAKUKAN PRESENSI (KAMERA AKTIF: BELUM CHECK-IN ATAU DITOLAK HR) --}}
                @else
                    @if($todayAttendance && $todayAttendance->isRejected())
                        <!-- Alert Penolakan Presensi oleh HR -->
                        <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 space-y-3 mb-5">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-rose-900">Foto Presensi Masuk Hari Ini Ditolak oleh HR</h4>
                                    <p class="text-xs text-rose-700 leading-relaxed">
                                        <strong>Alasan Penolakan:</strong> "{{ $todayAttendance->notes }}"
                                    </p>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/80 border border-rose-200 text-[11px] text-rose-800 font-semibold mt-1">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Waktu Check-In Awal: {{ $todayAttendance->checked_in_at->format('H:i:s') }} WIB (Status {{ $todayAttendance->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }} tetap dipertahankan).</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-rose-600 border-t border-rose-200/60 pt-2 font-medium">
                                Kamera telah dibuka kembali. Silakan ambil foto selfie ulang dengan pencahayaan jelas untuk memperbarui berkas bukti presensi Anda.
                            </p>
                        </div>
                    @endif
                    
                    <!-- Viewport Kamera Selfie Langsung WebRTC (Click-to-Start & Bounded Container) -->
                    <div class="relative w-full aspect-video max-h-[420px] bg-slate-900 rounded-2xl overflow-hidden shadow-inner">
                        
                        <!-- Video Stream dari WebRTC -->
                        <video id="cameraVideo" autoplay playsinline class="hidden absolute inset-0 w-full h-full object-cover transform scale-x-[-1]"></video>
                        
                        <!-- Canvas Tersembunyi untuk Snapshot Frame -->
                        <canvas id="captureCanvas" class="hidden"></canvas>

                        <!-- Preview Foto yang Sudah Ditangkap Sebelum Submit -->
                        <img id="snapshotPreview" alt="Snapshot" class="hidden absolute inset-0 w-full h-full object-cover z-10">

                        <!-- Overlay Standby (Kamera Mati Hingga Diklik/Tap) -->
                        <button
                            type="button"
                            id="cameraStandbyOverlay"
                            onclick="startCamera()"
                            class="absolute inset-0 z-10 w-full h-full bg-slate-900 hover:bg-slate-800/95 flex flex-col items-center justify-center p-6 text-center text-white transition cursor-pointer group focus:outline-none">
                            <div class="w-16 h-16 rounded-2xl bg-[#015987]/30 border border-sky-400/30 text-sky-300 flex items-center justify-center mb-3.5 group-hover:scale-105 transition">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-white">Klik layar ini untuk mengaktifkan kamera</p>
                            <p class="text-[11px] text-slate-300 mt-1">Pastikan izin kamera browser diizinkan</p>
                        </button>

                        <!-- HUD Badge Atas Kamera -->
                        <div class="absolute top-4 left-4 z-10 flex items-center gap-2 bg-slate-900/80 text-sky-200 text-[10px] font-semibold px-3 py-1.5 rounded-xl border border-sky-400/20 backdrop-blur-xs pointer-events-none">
                            <span id="cameraHudDot" class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span id="cameraHudText">Kamera Standby</span>
                        </div>

                        <!-- Fallback jika Akses Kamera Ditolak / Tidak Tersedia -->
                        <div id="cameraFallback" class="hidden absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-6 text-center text-white z-20 space-y-3">
                            <svg class="w-10 h-10 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-xs font-bold" id="cameraErrorMsg">Izin kamera belum diberikan pada peramban web ini.</p>
                            <button type="button" onclick="startCamera()" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl transition">
                                Coba Buka Kamera Lagi
                            </button>
                        </div>

                    </div>

                    <!-- Input Catatan Tambahan (Opsional) -->
                    <div class="space-y-1.5 pt-1">
                        <label for="presensiNotes" class="block text-xs font-bold text-slate-700">
                            Catatan Presensi <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="presensiNotes" 
                            placeholder="Tuliskan keterangan jika ada (contoh: WFH, dinas luar, dsb.)..." 
                            class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] shadow-xs transition"
                        >
                    </div>

                    <!-- Kontrol Shutter & Konfirmasi Pengiriman -->
                    <div class="space-y-3 pt-2">
                        
                        <!-- Mode 1: Tombol Shutter Ambil Foto (Default: Disabled sampai kamera aktif) -->
                        <div id="shutterControl">
                            <button 
                                type="button" 
                                id="btnShutter" 
                                onclick="takeSnapshot()"
                                disabled
                                class="w-full py-3.5 bg-[#015987] hover:bg-[#01486e] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $isRejectedToday ? 'Ambil Foto Selfie Ulang' : 'Ambil Foto Selfie & Presensi Masuk' }}</span>
                            </button>
                        </div>

                        <!-- Mode 2: Tombol Konfirmasi Setelah Foto Diambil -->
                        <div id="confirmControl" class="hidden grid grid-cols-2 gap-3">
                            <button 
                                type="button" 
                                onclick="retakePhoto()"
                                class="w-full py-3 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-xl transition">
                                Ambil Ulang Foto
                            </button>

                            <button 
                                type="button" 
                                id="btnSubmitAttendance"
                                onclick="submitAttendance()"
                                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $isRejectedToday ? 'Kirim Foto Ulang' : 'Kirim Presensi Sekarang' }}</span>
                            </button>
                        </div>

                    </div>

                    <!-- Form Tersembunyi Multipart FormData untuk Submit ke Backend Laravel -->
                    <form id="attendanceForm" method="POST" action="{{ route('intern.attendance.store') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" name="photo" id="hiddenPhotoInput">
                        <input type="hidden" name="notes" id="hiddenNotesInput">
                    </form>

                @endif

            </div>

        </div>

        <!-- ========================================================== -->
        <!-- RIWAYAT PRESENSI & IZIN PESERTA (FULL WIDTH)               -->
        <!-- ========================================================== -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                
                <!-- Header Riwayat & Filter Bulan -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm sm:text-base font-bold text-[#015987]">Riwayat Presensi Anda</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Catatan presensi masuk dan pengajuan izin/sakit Anda</p>
                    </div>

                    <!-- Dropdown Filter Bulan -->
                    <form method="GET" action="{{ route('intern.attendance.index') }}" class="flex items-center gap-1.5">
                        <select 
                            name="month" 
                            onchange="this.form.submit()" 
                            class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('M') }}
                                </option>
                            @endfor
                        </select>
                        <select 
                            name="year" 
                            onchange="this.form.submit()" 
                            class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium focus:outline-none">
                            @for($y = $now->year - 1; $y <= $now->year + 1; $y++)
                                <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>

                <!-- Daftar Riwayat Gabungan (Presensi + Izin/Sakit) -->
                @if($unifiedHistory->isEmpty())
                    <div class="text-center py-10 px-4 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Belum Ada Riwayat Presensi &amp; Izin</p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Data kehadiran atau pengajuan izin/sakit bulan ini belum tercatat.
                        </p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($unifiedHistory as $entry)
                            @if($entry['type'] === 'attendance')
                                @php $row = $entry['data']; @endphp
                                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-200 transition space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center gap-3">
                                            <!-- Thumbnail Foto Selfie Privat -->
                                            <div class="w-11 h-11 rounded-lg bg-slate-200 overflow-hidden border border-slate-200 flex-shrink-0 cursor-pointer"
                                                 onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->attendance_date->translatedFormat('d F Y') }}')">
                                                <img src="{{ route('media.private.show', $row->photo_media_id) }}" alt="Selfie" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-50 text-[#015987] border border-sky-200/70">Presensi</span>
                                                    <p class="text-xs font-bold text-slate-800">
                                                        {{ $row->attendance_date->translatedFormat('l, d M Y') }}
                                                    </p>
                                                </div>
                                                <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1 mt-1">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span>Check-in: {{ $row->checked_in_at->format('H:i') }} WIB</span>
                                                    <span class="mx-1 text-slate-300">•</span>
                                                    <button type="button" onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->attendance_date->translatedFormat('d F Y') }}')" class="text-[#015987] hover:underline font-semibold">
                                                        Lihat Foto Bukti
                                                    </button>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Badge Status Tepat Waktu / Terlambat / Ditolak -->
                                        @if($row->isRejected())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border bg-rose-50 text-rose-700 border-rose-200 self-start sm:self-center">
                                                <span class="w-1 h-1 rounded-full bg-rose-600"></span>
                                                Ditolak HR
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border self-start sm:self-center {{ $row->isOnTime() ? 'bg-teal-50 text-teal-600 border-teal-200' : 'bg-rose-50 text-rose-600 border-rose-200' }}">
                                                <span class="w-1 h-1 rounded-full {{ $row->isOnTime() ? 'bg-teal-500' : 'bg-rose-500' }}"></span>
                                                {{ $row->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Catatan Peserta / Catatan Review HR / Alasan Penolakan -->
                                    @if($row->isRejected())
                                        <div class="text-[11px] pt-1.5 border-t border-slate-100 flex flex-col gap-0.5">
                                            <p class="text-rose-600 font-medium">
                                                <span class="font-semibold text-rose-700">Alasan Penolakan HR:</span> {{ $row->notes }}
                                            </p>
                                            @if($row->isReviewed() && $row->reviewer)
                                                <p class="text-[10px] text-slate-400">
                                                    Ditinjau oleh HR ({{ $row->reviewer->name }})
                                                </p>
                                            @endif
                                        </div>
                                    @elseif($row->notes || $row->isReviewed())
                                        <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-100 flex flex-col gap-0.5">
                                            @if($row->notes)
                                                <p><span class="font-semibold text-slate-600">Catatan:</span> {{ $row->notes }}</p>
                                            @endif
                                            @if($row->isReviewed() && $row->reviewer)
                                                <p class="text-[10px] text-sky-700 font-medium">
                                                    Ditinjau oleh HR ({{ $row->reviewer->name }})
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                @php $leave = $entry['data']; @endphp
                                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-200 transition space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-lg {{ $leave->isSick() ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-amber-50 text-amber-600 border-amber-200' }} border flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $leave->isSick() ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                        {{ $leave->isSick() ? 'Sakit' : 'Izin' }} ({{ $leave->duration_days }} Hari)
                                                    </span>
                                                    <p class="text-xs font-bold text-slate-800">
                                                        {{ $leave->start_date->translatedFormat('d M Y') }}@if($leave->start_date->ne($leave->end_date)) — {{ $leave->end_date->translatedFormat('d M Y') }}@endif
                                                    </p>
                                                </div>
                                                <p class="text-[11px] text-slate-500 font-medium flex flex-wrap items-center gap-1.5 mt-1">
                                                    <span>Diajukan: {{ $leave->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                                                    @if($leave->evidence)
                                                        <span class="text-slate-300">•</span>
                                                        <a href="{{ route('media.private.show', $leave->evidence_media_id) }}" target="_blank" class="text-[#015987] hover:underline font-semibold">
                                                            Lihat Bukti
                                                        </a>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Badge Status Izin -->
                                        @if($leave->isPending())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border bg-amber-50 text-amber-700 border-amber-200 self-start sm:self-center">
                                                <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Verifikasi
                                            </span>
                                        @elseif($leave->isApproved())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 self-start sm:self-center">
                                                <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                                Disetujui HR
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border bg-rose-50 text-rose-700 border-rose-200 self-start sm:self-center">
                                                <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                                Ditolak HR
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-slate-600 pt-1.5 border-t border-slate-100 space-y-0.5">
                                        <p><span class="font-semibold text-slate-700">Alasan:</span> {{ $leave->reason }}</p>
                                        @if($leave->review_notes)
                                            <p class="{{ $leave->isRejected() ? 'text-rose-600' : 'text-emerald-700' }}">
                                                <span class="font-semibold">Catatan HR:</span> {{ $leave->review_notes }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($unifiedHistory->hasPages())
                        <div class="pt-3 border-t border-slate-100">
                            {{ $unifiedHistory->links() }}
                        </div>
                    @endif
                @endif

            </div>

        </div>

    </div>

</div>

@push('modals')
<!-- Modal Pop-up Preview Foto Selfie Resolusi Penuh -->
<div id="imagePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-md w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h5 class="text-xs font-bold text-[#015987]" id="previewModalTitle">Bukti Foto Selfie</h5>
            <button type="button" onclick="closeImagePreviewModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-4 bg-slate-900 flex items-center justify-center">
            <img id="previewModalImg" src="" alt="Selfie" class="max-h-[380px] w-auto object-contain rounded-lg">
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
    let mediaStream = null;
    let capturedBlob = null;

    // Jam Real-time Server WIB
    function updateLiveClock() {
        const clockEl = document.getElementById('liveClockWIB');
        if (!clockEl) return;
        const now = new Date();
        // Konversi ke UTC lalu offset +7 Jam WIB
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const wibTime = new Date(utc + (3600000 * 7));
        const hours = String(wibTime.getHours()).padStart(2, '0');
        const minutes = String(wibTime.getMinutes()).padStart(2, '0');
        const seconds = String(wibTime.getSeconds()).padStart(2, '0');
        clockEl.innerText = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateLiveClock, 1000);

    // Monitoring Status Online / Offline PWA
    function checkNetworkStatus() {
        const notice = document.getElementById('offlineNotice');
        if (!notice) return;
        if (!navigator.onLine) {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
    }
    window.addEventListener('online', checkNetworkStatus);
    window.addEventListener('offline', checkNetworkStatus);
    checkNetworkStatus();

    // Membuka Aliran Kamera Webcam WebRTC (hanya dipanggil saat layar diklik/tap)
    async function startCamera() {
        const video = document.getElementById('cameraVideo');
        const standby = document.getElementById('cameraStandbyOverlay');
        const fallback = document.getElementById('cameraFallback');
        const errorMsg = document.getElementById('cameraErrorMsg');
        const btnShutter = document.getElementById('btnShutter');
        const hudText = document.getElementById('cameraHudText');
        const hudDot = document.getElementById('cameraHudDot');

        if (!video) return;

        try {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
            }

            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            video.srcObject = mediaStream;
            video.classList.remove('hidden');
            if (standby) standby.classList.add('hidden');
            if (fallback) fallback.classList.add('hidden');
            if (btnShutter) btnShutter.disabled = false;
            if (hudText) hudText.innerText = 'Live Camera';
            if (hudDot) hudDot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
        } catch (err) {
            console.error('Kamera gagal diakses:', err);
            if (video) video.classList.add('hidden');
            if (standby) standby.classList.add('hidden');
            if (btnShutter) btnShutter.disabled = true;
            if (hudText) hudText.innerText = 'Kamera Nonaktif';
            if (hudDot) hudDot.className = 'w-2 h-2 rounded-full bg-rose-500';
            if (fallback) {
                fallback.classList.remove('hidden');
                errorMsg.innerText = 'Kamera tidak dapat diakses (' + err.message + '). Pastikan peramban mengizinkan akses kamera.';
            }
        }
    }

    // Mengambil Foto Snapshot dari Video ke Canvas
    function takeSnapshot() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('captureCanvas');
        const preview = document.getElementById('snapshotPreview');
        const guide = document.getElementById('biometricGuide');
        const shutterControl = document.getElementById('shutterControl');
        const confirmControl = document.getElementById('confirmControl');
        const hudText = document.getElementById('cameraHudText');

        if (!video || !canvas || !preview) return;

        const width = video.videoWidth || 640;
        const height = video.videoHeight || 480;

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        // Mirroring balik agar hasil foto tidak terbalik
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, width, height);

        canvas.toBlob(function(blob) {
            if (!blob) {
                alert('Gagal mengambil gambar.');
                return;
            }

            capturedBlob = blob;
            preview.src = URL.createObjectURL(blob);
            preview.classList.remove('hidden');
            video.classList.add('hidden');
            if (guide) guide.classList.add('hidden');

            shutterControl.classList.add('hidden');
            confirmControl.classList.remove('hidden');

            if (hudText) {
                hudText.innerText = 'Snapshot Biometrik Siap Dikirim';
            }
        }, 'image/jpeg', 0.85);
    }

    // Mengambil Ulang Foto
    function retakePhoto() {
        const video = document.getElementById('cameraVideo');
        const preview = document.getElementById('snapshotPreview');
        const guide = document.getElementById('biometricGuide');
        const shutterControl = document.getElementById('shutterControl');
        const confirmControl = document.getElementById('confirmControl');
        const hudText = document.getElementById('cameraHudText');

        capturedBlob = null;
        if (preview) preview.classList.add('hidden');
        if (video) video.classList.remove('hidden');
        if (guide) guide.classList.remove('hidden');

        if (shutterControl) shutterControl.classList.remove('hidden');
        if (confirmControl) confirmControl.classList.add('hidden');

        if (hudText) {
            hudText.innerText = 'Live Camera';
        }
    }

    // Mengirim Presensi ke Backend
    function submitAttendance() {
        if (!navigator.onLine) {
            alert('Koneksi internet Anda sedang terputus. Presensi membutuhkan jaringan online aktif.');
            return;
        }

        if (!capturedBlob) {
            alert('Foto selfie belum diambil.');
            return;
        }

        const btnSubmit = document.getElementById('btnSubmitAttendance');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memproses Presensi...</span>
        `;

        const hiddenFileInput = document.getElementById('hiddenPhotoInput');
        const hiddenNotesInput = document.getElementById('hiddenNotesInput');
        const userNotes = document.getElementById('presensiNotes');
        const form = document.getElementById('attendanceForm');

        const file = new File([capturedBlob], 'selfie_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        hiddenFileInput.files = dataTransfer.files;

        if (userNotes) {
            hiddenNotesInput.value = userNotes.value;
        }

        form.submit();
    }

    // Modal Preview Foto
    function openImagePreviewModal(url, title) {
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('previewModalImg');
        const titleEl = document.getElementById('previewModalTitle');
        img.src = url;
        if (titleEl) titleEl.innerText = title;
        modal.classList.remove('hidden');
    }

    function closeImagePreviewModal() {
        document.getElementById('imagePreviewModal').classList.add('hidden');
    }

    // Kamera tidak langsung aktif saat halaman dibuka (Click-to-Start melalui overlay standby).

    // Matikan stream kamera saat berpindah halaman
    window.addEventListener('beforeunload', function() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endpush
