@extends('layouts.app')

@section('title', 'Monitoring Presensi Peserta - Admin')
@section('page-title', 'Monitoring & Rekap Presensi Peserta Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengawasan Kehadiran Harian & Otorisasi Peninjauan Status')
@section('search-placeholder', 'Cari peserta atau email...')

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

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1 shadow-xs">
            <div class="flex items-center gap-2 text-rose-700 font-bold">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Gagal memperbarui status presensi:</span>
            </div>
            <ul class="list-disc list-inside pl-6 text-rose-600 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN REKAPITULASI HARIAN               -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total Peserta Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Total Peserta Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $dailyRecap['total_active_interns'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Akun aktif terdaftar
                </p>
            </div>
        </div>

        <!-- Card 2: Hadir Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Presensi Tercatat</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $dailyRecap['total_present'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Hadir</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1">
                    <span>{{ $dailyRecap['on_time_count'] }} Tepat Waktu • {{ $dailyRecap['late_count'] }} Terlambat</span>
                </p>
            </div>
        </div>

        <!-- Card 3: Izin & Sakit Disetujui -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Izin/Sakit Sah</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $dailyRecap['approved_leaves_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Disetujui</span>
                </div>
                <p class="text-[11px] font-semibold text-indigo-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Pengecualian sah presensi
                </p>
            </div>
        </div>

        <!-- Card 4: Izin Menunggu Verifikasi -->
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $dailyRecap['pending_leaves_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Pengajuan</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Perlu ditinjau di modul Izin
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. TOOLBAR FILTER & PENCARIAN TANGGAL                          -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            
            <!-- Filter Tanggal Presensi -->
            <div class="lg:col-span-3 space-y-1.5">
                <label for="filterDate" class="block text-xs font-bold text-slate-700">Tanggal Pemantauan</label>
                <input 
                    type="date" 
                    id="filterDate" 
                    name="date" 
                    value="{{ $selectedDate }}" 
                    class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                >
            </div>

            <!-- Filter Status (Semua / Tepat Waktu / Terlambat) -->
            <div class="lg:col-span-3 space-y-1.5">
                <label for="filterStatus" class="block text-xs font-bold text-slate-700">Status Kehadiran</label>
                <select 
                    id="filterStatus" 
                    name="status" 
                    class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]">
                    <option value="">Semua Status</option>
                    <option value="on_time" {{ $statusFilter === 'on_time' ? 'selected' : '' }}>Tepat Waktu (on_time)</option>
                    <option value="late" {{ $statusFilter === 'late' ? 'selected' : '' }}>Terlambat (late)</option>
                </select>
            </div>

            <!-- Input Pencarian Nama Peserta -->
            <div class="lg:col-span-4 space-y-1.5">
                <label for="filterSearch" class="block text-xs font-bold text-slate-700">Cari Peserta Magang</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="filterSearch" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Nama peserta atau email..." 
                        class="w-full pl-9 pr-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Tombol Terapkan & Reset -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Filter
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 3. TABEL MONITORING PRESENSI PESERTA                           -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
        
        <!-- Header Kartu Tabel -->
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-[#015987]">
                    Presensi Masuk Tanggal {{ Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Menampilkan {{ $attendances->count() }} catatan presensi tervalidasi
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Batas Tepat Waktu:</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-[#015987] border border-sky-200/70">
                    08:05:59 WIB
                </span>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Peserta Magang</th>
                        <th class="py-4 px-5">Waktu Masuk (WIB)</th>
                        <th class="py-4 px-5 text-center">Foto Bukti</th>
                        <th class="py-4 px-5">Status</th>
                        <th class="py-4 px-5">Catatan / Review</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- 1. Peserta Magang -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($row->intern->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">{{ $row->intern->name }}</p>
                                        <p class="text-[10px] text-slate-400 mt-1">{{ $row->intern->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Waktu Masuk Server WIB -->
                            <td class="py-4 px-5">
                                <span class="font-semibold {{ $row->isOnTime() ? 'text-slate-700' : 'text-rose-500' }} flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 {{ $row->isOnTime() ? 'text-slate-400' : 'text-rose-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $row->checked_in_at->format('H:i:s') }}
                                </span>
                            </td>

                            <!-- 3. Foto Bukti Selfie (Privat) -->
                            <td class="py-4 px-5 text-center">
                                <div class="w-9 h-9 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200 cursor-pointer hover:ring-2 hover:ring-[#015987] transition shadow-2xs"
                                     onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->checked_in_at->format('H:i') }} WIB')">
                                    <img src="{{ route('media.private.show', $row->photo_media_id) }}" alt="Foto Selfie" class="w-full h-full object-cover">
                                </div>
                            </td>

                            <!-- 4. Status Presensi -->
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $row->isOnTime() ? 'bg-teal-50 text-teal-600 border-teal-200' : 'bg-rose-50 text-rose-600 border-rose-200' }}">
                                    <span class="w-1 h-1 rounded-full {{ $row->isOnTime() ? 'bg-teal-500' : 'bg-rose-500' }}"></span>
                                    {{ $row->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}
                                </span>
                            </td>

                            <!-- 5. Catatan / Status Koreksi HR -->
                            <td class="py-4 px-5 max-w-xs">
                                @if($row->isReviewed())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200/80 mb-1">
                                        <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Ditinjau: {{ $row->reviewer->name }}
                                    </span>
                                @endif
                                <p class="text-[11px] text-slate-500 truncate" title="{{ $row->notes }}">
                                    {{ $row->notes ?: '-' }}
                                </p>
                            </td>

                            <!-- 6. Aksi (Koreksi Status) -->
                            <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                <button 
                                    type="button" 
                                    onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->checked_in_at->format('H:i') }} WIB')"
                                    class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Foto
                                </button>
                                
                                <button 
                                    type="button" 
                                    onclick="openCorrectionModal('{{ $row->id }}', '{{ $row->intern->name }}', '{{ $row->status }}', '{{ addslashes($row->notes ?? '') }}', '{{ route('admin.attendance.update', $row) }}')"
                                    class="px-2.5 py-1 text-[11px] font-semibold text-[#015987] bg-sky-50 border border-sky-200/80 rounded-lg hover:bg-sky-100 transition shadow-2xs">
                                    Koreksi
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Tidak Ada Data Presensi</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Belum ada peserta yang melakukan presensi masuk pada tanggal terpilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- 4. MODAL KOREKSI / PENINJAUAN STATUS PRESENSI OLEH ADMIN/HR   -->
<!-- ============================================================== -->
<div id="correctionModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl border border-slate-200/80 shadow-xl overflow-hidden">
        
        <div class="px-6 py-4 bg-gradient-to-r from-sky-50 to-indigo-50 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-[#015987]">Koreksi Status Presensi</h4>
                <p class="text-[11px] text-slate-500 mt-0.5" id="correctionModalSubtitle">Peserta Magang</p>
            </div>
            <button type="button" onclick="closeCorrectionModal()" class="text-slate-400 hover:text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="correctionForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PATCH')

            <!-- Pilihan Status Baru -->
            <div class="space-y-1.5">
                <label for="corrStatus" class="block text-xs font-bold text-slate-700">Status Presensi <span class="text-rose-500">*</span></label>
                <select id="corrStatus" name="status" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]">
                    <option value="on_time">Tepat Waktu (on_time)</option>
                    <option value="late">Terlambat (late)</option>
                </select>
            </div>

            <!-- Catatan / Alasan Koreksi -->
            <div class="space-y-1.5">
                <label for="corrNotes" class="block text-xs font-bold text-slate-700">Alasan Koreksi / Catatan HR <span class="text-rose-500">*</span></label>
                <textarea 
                    id="corrNotes" 
                    name="notes" 
                    rows="3" 
                    required 
                    placeholder="Contoh: Koreksi jam kehadiran sesuai laporan kendala teknis jaringan / izin khusus PIC..."
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] resize-none"
                ></textarea>
                <p class="text-[10px] text-slate-400">Setiap perubahan status akan dicatat ke dalam audit trail sistem.</p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCorrectionModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Simpan Koreksi
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal Preview Foto Selfie Resolusi Penuh -->
<div id="imagePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
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
            <img id="previewModalImg" src="" alt="Selfie" class="max-h-[400px] w-auto object-contain rounded-lg">
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
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

    function openCorrectionModal(attendanceId, internName, status, notes, updateUrl) {
        const modal = document.getElementById('correctionModal');
        const form = document.getElementById('correctionForm');
        const subtitle = document.getElementById('correctionModalSubtitle');
        const statusSelect = document.getElementById('corrStatus');
        const notesTextarea = document.getElementById('corrNotes');

        form.action = updateUrl;
        subtitle.innerText = 'Peserta: ' + internName;
        statusSelect.value = status;
        notesTextarea.value = notes || '';

        modal.classList.remove('hidden');
    }

    function closeCorrectionModal() {
        document.getElementById('correctionModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImagePreviewModal();
            closeCorrectionModal();
        }
    });
</script>
@endpush
