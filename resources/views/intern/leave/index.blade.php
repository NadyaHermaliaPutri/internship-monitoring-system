@extends('layouts.app')

@section('title', 'Pengajuan Izin & Sakit - Peserta')
@section('page-title', 'Pengajuan Izin & Sakit')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Permohonan Ketidakhadiran Resmi & Pengunggahan Bukti')
@section('search-placeholder', 'Cari riwayat permohonan...')

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
                <span>{{ session('error') ?? 'Gagal mengirim permohonan izin/sakit:' }}</span>
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

    <!-- Banner Peringatan Offline PWA -->
    <div id="offlineNotice" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-3 shadow-xs">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-1.414-1.414m-2.828-2.828A9 9 0 015.636 5.636m0 0L3 3m5.464 5.464a5 5 0 011.414-1.414m2.828 2.828a5 5 0 011.415 1.414"/>
        </svg>
        <div>
            <p class="font-bold">Koneksi Internet Terputus</p>
            <p class="text-[11px] text-amber-700 font-medium mt-0.5">Pengajuan izin memerlukan jaringan internet aktif. Berkas bukti tidak dapat diunggah saat offline.</p>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN STATUS PERMOHONAN                -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Menunggu Verifikasi HR -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Menunggu Verifikasi</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalPending }}</span>
                    <span class="text-xs text-slate-500 font-medium">Permohonan</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Status Pending
                </p>
            </div>
        </div>

        <!-- Card 2: Disetujui HR -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Disetujui HR</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalApproved }}</span>
                    <span class="text-xs text-slate-500 font-medium">Izin Sah</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Dikecualikan dari presensi
                </p>
            </div>
        </div>

        <!-- Card 3: Ditolak -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Ditolak</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalRejected }}</span>
                    <span class="text-xs text-slate-500 font-medium">Pengajuan</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Lihat catatan alasan penolakan
                </p>
            </div>
        </div>

        <!-- Card 4: Ketentuan Berkas -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Format Bukti</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-lg font-bold text-[#015987]">PDF / Gambar</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-500 mt-1.5 flex items-center gap-1">
                    <span>Maksimal 5 MB • Tersimpan privat</span>
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID KONTEN UTAMA: FORMULIR PENGAJUAN (KIRI) & RIWAYAT (KANAN) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (LEBAR 6/12): FORMULIR PERMOHONAN IZIN / SAKIT -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-6">
            
            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-[#015987]">Formulir Permohonan Izin / Sakit</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Lengkapi formulir dengan jujur dan sertakan berkas bukti yang sah</p>
                </div>
                <span class="text-[11px] font-semibold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200/70">
                    WIB Time
                </span>
            </div>

            <form method="POST" action="{{ route('intern.leave.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- 1. Pilihan Jenis Permohonan (Radio Cards) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Jenis Permohonan <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <!-- Opsi Sakit -->
                        <label class="cursor-pointer">
                            <input type="radio" name="leave_type" id="typeSick" value="sick" {{ old('leave_type') === 'sick' ? 'checked' : '' }} class="peer sr-only">
                            <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/60 transition text-center space-y-1 hover:border-slate-300">
                                <span class="inline-flex w-7 h-7 rounded-full bg-rose-100 text-rose-600 items-center justify-center mx-auto mb-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                </span>
                                <p class="text-xs font-bold text-slate-800">Sakit</p>
                                <span class="inline-block text-[10px] font-semibold text-rose-600 bg-rose-100/70 px-2 py-0.5 rounded">
                                    Wajib Surat Dokter
                                </span>
                            </div>
                        </label>

                        <!-- Opsi Izin -->
                        <label class="cursor-pointer">
                            <input type="radio" name="leave_type" id="typeLeave" value="leave" {{ old('leave_type', 'leave') === 'leave' ? 'checked' : '' }} class="peer sr-only">
                            <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/60 transition text-center space-y-1 hover:border-slate-300">
                                <span class="inline-flex w-7 h-7 rounded-full bg-sky-100 text-[#015987] items-center justify-center mx-auto mb-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <p class="text-xs font-bold text-slate-800">Izin Keperluan</p>
                                <span class="inline-block text-[10px] font-semibold text-[#015987] bg-sky-100/70 px-2 py-0.5 rounded">
                                    Surat Dispensasi / Bukti
                                </span>
                            </div>
                        </label>

                    </div>
                </div>

                <!-- 2. Rentang Tanggal (Mulai & Selesai) -->
                <div class="space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="startDate" class="block text-xs font-bold text-slate-700">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                id="startDate" 
                                name="start_date" 
                                value="{{ old('start_date', date('Y-m-d')) }}" 
                                required 
                                onchange="calculateDuration()"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="endDate" class="block text-xs font-bold text-slate-700">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                id="endDate" 
                                name="end_date" 
                                value="{{ old('end_date', date('Y-m-d')) }}" 
                                required 
                                onchange="calculateDuration()"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                            >
                        </div>
                    </div>

                    <!-- Estimasi Durasi Hari -->
                    <div class="text-right">
                        <span id="durationBadge" class="text-[11px] font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200/80">
                            Durasi: 1 Hari Kalender
                        </span>
                    </div>
                </div>

                <!-- 3. Alasan Permohonan -->
                <div class="space-y-1.5">
                    <label for="leaveReason" class="block text-xs font-bold text-slate-700">
                        Alasan Permohonan <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="leaveReason" 
                        name="reason" 
                        rows="3" 
                        required 
                        minlength="5"
                        maxlength="1000"
                        placeholder="Jelaskan alasan izin atau kondisi sakit secara jelas..." 
                        class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs resize-none"
                    >{{ old('reason') }}</textarea>
                </div>

                <!-- 4. Unggah Berkas Bukti (Dropzone) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">
                            Unggah Dokumen Bukti <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Maks. 5 MB (PDF, JPG, PNG)</span>
                    </div>

                    <div class="relative border-2 border-dashed border-slate-200 hover:border-[#015987] rounded-2xl p-5 text-center bg-slate-50/50 transition cursor-pointer group">
                        <input 
                            type="file" 
                            name="evidence" 
                            id="evidenceFileInput" 
                            required 
                            accept=".pdf,.jpg,.jpeg,.png"
                            onchange="handleEvidenceSelect(this)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        >
                        
                        <div id="dropzoneEmpty" class="space-y-2">
                            <div class="w-10 h-10 rounded-full bg-sky-50 text-[#015987] mx-auto flex items-center justify-center group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#015987]">Pilih Dokumen Bukti</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Klik untuk menelusuri atau seret berkas ke sini</p>
                            </div>
                        </div>

                        <div id="dropzoneFilled" class="hidden flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center gap-2.5 truncate">
                                <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div class="text-left truncate">
                                    <p class="text-xs font-bold text-slate-800 truncate" id="selectedFileName">surat_dokter.pdf</p>
                                    <p class="text-[10px] text-slate-400" id="selectedFileSize">1.2 MB</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600">Terpilih</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>Kirim Permohonan Izin / Sakit</span>
                </button>

            </form>

        </div>

        <!-- KOLOM KANAN (LEBAR 6/12): RIWAYAT & CATATAN KEPUTUSAN HR -->
        <div class="lg:col-span-6 space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                
                <!-- Header Riwayat & Filter Status -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-[#015987]">Riwayat Permohonan Anda</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Status dan catatan pertimbangan dari Admin/HR</p>
                    </div>

                    <!-- Filter Status Buttons -->
                    <div class="flex items-center gap-1.5 text-xs font-medium">
                        <a href="{{ route('intern.leave.index') }}" 
                           class="px-2.5 py-1 rounded-lg text-xs {{ ! $statusFilter ? 'bg-[#015987] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Semua
                        </a>
                        <a href="{{ route('intern.leave.index', ['status' => 'pending']) }}" 
                           class="px-2.5 py-1 rounded-lg text-xs {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white font-bold' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                            Pending
                        </a>
                        <a href="{{ route('intern.leave.index', ['status' => 'approved']) }}" 
                           class="px-2.5 py-1 rounded-lg text-xs {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            Disetujui
                        </a>
                        <a href="{{ route('intern.leave.index', ['status' => 'rejected']) }}" 
                           class="px-2.5 py-1 rounded-lg text-xs {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                            Ditolak
                        </a>
                    </div>
                </div>

                <!-- Feed Kartu Riwayat -->
                @if($leaveRequests->isEmpty())
                    <div class="text-center py-12 px-4 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Belum Ada Pengajuan Izin/Sakit</p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Semua permohonan yang Anda ajukan akan muncul di sini beserta catatan keputusannya.
                        </p>
                    </div>
                @else
                    <div class="space-y-3.5 max-h-[500px] overflow-y-auto pr-1">
                        @foreach($leaveRequests as $item)
                            <div class="p-4 rounded-xl border border-slate-200/80 bg-white hover:border-[#015987] transition shadow-2xs space-y-3">
                                
                                <!-- Baris Atas: Tag Jenis & Badge Status -->
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $item->isSick() ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-sky-50 text-[#015987] border border-sky-200' }}">
                                        {{ $item->isSick() ? 'Sakit' : 'Izin Keperluan' }} ({{ $item->duration_days }} Hari)
                                    </span>

                                    <!-- Badge Status Keputusan -->
                                    @if($item->isPending())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            PENDING
                                        </span>
                                    @elseif($item->isApproved())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            DISETUJUI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            DITOLAK
                                        </span>
                                    @endif
                                </div>

                                <!-- Rentang Tanggal -->
                                <div>
                                    <p class="text-xs font-bold text-slate-800">
                                        {{ $item->start_date->translatedFormat('d M Y') }} — {{ $item->end_date->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        {{ $item->reason }}
                                    </p>
                                </div>

                                <!-- Lampiran Berkas Bukti -->
                                @if($item->evidence)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                                        <a href="{{ route('media.private.show', $item->evidence_media_id) }}" target="_blank" class="flex items-center gap-2 text-sky-700 hover:text-[#015987] font-semibold truncate text-[11px]">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span class="truncate">{{ $item->evidence->original_name }}</span>
                                            <span class="text-slate-400 font-normal">({{ $item->evidence->formatted_size }})</span>
                                        </a>

                                        <a href="{{ route('media.private.show', ['mediaFile' => $item->evidence_media_id, 'download' => 1]) }}" class="text-slate-400 hover:text-slate-600 p-1" title="Unduh Berkas">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    </div>
                                @endif

                                <!-- Catatan Keputusan dari HR (Jika Sudah Diproses) -->
                                @if($item->reviewed_by)
                                    <div class="p-3 rounded-xl {{ $item->isApproved() ? 'bg-emerald-50/50 border border-emerald-200/70' : 'bg-rose-50/50 border border-rose-200/70' }} space-y-1">
                                        <div class="flex items-center justify-between text-[10px] font-bold {{ $item->isApproved() ? 'text-emerald-700' : 'text-rose-700' }}">
                                            <span>Keputusan HR ({{ $item->reviewer->name }})</span>
                                            <span>{{ $item->reviewed_at?->translatedFormat('d M Y, H:i') }} WIB</span>
                                        </div>
                                        <p class="text-xs {{ $item->isApproved() ? 'text-emerald-900' : 'text-rose-900' }}">
                                            {{ $item->review_notes ?: ($item->isApproved() ? 'Permohonan telah disetujui sesuai bukti.' : 'Permohonan ditolak oleh HR.') }}
                                        </p>
                                    </div>
                                @endif

                                <div class="text-[10px] text-slate-400 text-right">
                                    Diajukan pada: {{ $item->created_at->translatedFormat('d F Y, H:i') }} WIB
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="pt-2 border-t border-slate-100">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif

            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function calculateDuration() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const badge = document.getElementById('durationBadge');

        if (!start || !end) return;

        const date1 = new Date(start);
        const date2 = new Date(end);

        if (date2 < date1) {
            badge.innerText = 'Tanggal selesai tidak valid!';
            badge.className = 'text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200';
            return;
        }

        const diffTime = Math.abs(date2 - date1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        badge.innerText = `Durasi: ${diffDays} Hari Kalender`;
        badge.className = 'text-[11px] font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200/80';
    }

    function handleEvidenceSelect(input) {
        const emptyBox = document.getElementById('dropzoneEmpty');
        const filledBox = document.getElementById('dropzoneFilled');
        const nameEl = document.getElementById('selectedFileName');
        const sizeEl = document.getElementById('selectedFileSize');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            nameEl.innerText = file.name;
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            sizeEl.innerText = `${sizeInMb} MB`;

            emptyBox.classList.add('hidden');
            filledBox.classList.remove('hidden');
        } else {
            emptyBox.classList.remove('hidden');
            filledBox.classList.add('hidden');
        }
    }

    // Monitoring Status Online / Offline
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
</script>
@endpush
