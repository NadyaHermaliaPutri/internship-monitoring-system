@extends('layouts.app')

@section('title', 'Verifikasi Izin & Sakit Peserta - Admin')
@section('page-title', 'Persetujuan & Verifikasi Izin / Sakit')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Tinjau Surat Bukti Medis & Penetapan Keputusan Sah Presensi')
@section('search-placeholder', 'Cari nama atau email peserta...')

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

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
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
                <span>Terdapat kesalahan saat memproses keputusan izin:</span>
            </div>
            <ul class="list-disc list-inside pl-6 text-rose-600 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PENGAJUAN IZIN & SAKIT             -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Menunggu Verifikasi (Pending) - Priority -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px] relative overflow-hidden">
            @if($totalPending > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-amber-400"></div>
            @endif
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
                    <span class="text-2xl sm:text-3xl font-extrabold {{ $totalPending > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $totalPending }}</span>
                    <span class="text-xs text-slate-500 font-medium">Pengajuan</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Perlu keputusan Admin/HR
                </p>
            </div>
        </div>

        <!-- Card 2: Disetujui HR (Approved) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Izin Disetujui</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600">{{ $totalApproved }}</span>
                    <span class="text-xs text-slate-500 font-medium">Disetujui</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Pengecualian sah presensi EWS
                </p>
            </div>
        </div>

        <!-- Card 3: Ditolak (Rejected) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Pengajuan Ditolak</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-rose-600">{{ $totalRejected }}</span>
                    <span class="text-xs text-slate-500 font-medium">Ditolak</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Tidak sah / dokumen tidak valid
                </p>
            </div>
        </div>

        <!-- Card 4: Total Semua Pengajuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Total Semua Permohonan</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#015987] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalAll }}</span>
                    <span class="text-xs text-slate-500 font-medium">Total</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-500 mt-1.5">
                    Historis pengajuan sistem
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. FILTER & PENCARIAN PENGAJUAN                                 -->
    <!-- ============================================================== -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs">
        <form method="GET" action="{{ route('admin.leave.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                
                <!-- Cari Nama / Email -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search ?? '' }}" 
                        placeholder="Cari peserta / email..."
                        class="w-full pl-9 pr-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                    >
                </div>

                <!-- Filter Status -->
                <div>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                    >
                        <option value="">Semua Status Pengajuan</option>
                        <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi (Pending)</option>
                        <option value="approved" {{ ($statusFilter ?? '') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="rejected" {{ ($statusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    </select>
                </div>

                <!-- Filter Jenis -->
                <div>
                    <select 
                        name="type" 
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                    >
                        <option value="">Semua Jenis (Izin & Sakit)</option>
                        <option value="sick" {{ ($typeFilter ?? '') === 'sick' ? 'selected' : '' }}>Sakit (Surat Dokter)</option>
                        <option value="leave" {{ ($typeFilter ?? '') === 'leave' ? 'selected' : '' }}>Izin Keperluan</option>
                    </select>
                </div>

            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-2xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter Data</span>
                </button>

                @if($statusFilter || $typeFilter || $search)
                    <a href="{{ route('admin.leave.index') }}" class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 3. TABEL DAFTAR PENGAJUAN IZIN / SAKIT                          -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Permohonan Izin / Sakit Peserta Magang</h3>
                <p class="text-xs text-slate-400 mt-0.5">Seluruh berkas bukti medis disimpan privat pada disk terproteksi</p>
            </div>
            <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-3 py-1 rounded-full">
                Total: {{ $leaveRequests->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Peserta Magang</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Periode Tanggal</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Alasan Pengajuan</th>
                        <th class="py-3 px-4 text-center">Bukti Pendukung</th>
                        <th class="py-3 px-4">Status & Keputusan HR</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                    @forelse($leaveRequests as $item)
                        <tr class="hover:bg-slate-50/60 transition {{ $item->isPending() ? 'bg-amber-50/20' : '' }}">
                            
                            <!-- Peserta -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#015987]/10 text-[#015987] font-bold flex items-center justify-center flex-shrink-0 text-xs">
                                        {{ strtoupper(substr($item->intern->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $item->intern->name ?? 'Peserta' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $item->intern->email ?? '-' }}</p>
                                        @if($item->intern->institution_name ?? false)
                                            <p class="text-[10px] text-sky-700 font-medium">{{ $item->intern->institution_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Jenis -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($item->isSick())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#015987]"></span>
                                        Izin
                                    </span>
                                @endif
                            </td>

                            <!-- Periode Tanggal -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <p class="font-semibold text-slate-800">{{ $item->start_date->translatedFormat('d M Y') }}</p>
                                @if($item->start_date->ne($item->end_date))
                                    <p class="text-[11px] text-slate-400">s/d {{ $item->end_date->translatedFormat('d M Y') }}</p>
                                @endif
                            </td>

                            <!-- Durasi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px]">
                                    {{ $item->getDurationDays() }} Hari
                                </span>
                            </td>

                            <!-- Alasan -->
                            <td class="py-4 px-4 max-w-[220px]">
                                <p class="line-clamp-2 text-slate-600 text-xs" title="{{ $item->reason }}">
                                    {{ $item->reason }}
                                </p>
                            </td>

                            <!-- Bukti Pendukung -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($item->evidence)
                                    @php
                                        $mediaUrl = route('media.private.show', $item->evidence->id);
                                        $isPdf = $item->evidence->isPdf();
                                    @endphp
                                    <button 
                                        type="button" 
                                        onclick="openEvidenceModal('{{ $mediaUrl }}', '{{ addslashes($item->evidence->original_name) }}', '{{ $item->evidence->readable_file_size }}', {{ $isPdf ? 'true' : 'false' }}, '{{ addslashes($item->intern->name ?? 'Peserta') }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-sky-200 bg-sky-50 hover:bg-sky-100/70 text-[#015987] text-xs font-semibold transition"
                                    >
                                        @if($isPdf)
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span>PDF Dokumen</span>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Lihat Gambar</span>
                                        @endif
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Tidak ada berkas</span>
                                @endif
                            </td>

                            <!-- Status & Keputusan HR -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($item->isPending())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <svg class="w-3 h-3 text-amber-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Menunggu Review
                                    </span>
                                @elseif($item->isApproved())
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Disetujui HR
                                        </span>
                                        @if($item->reviewer)
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                Oleh: {{ $item->reviewer->name }} ({{ $item->reviewed_at?->translatedFormat('d/m/y H:i') }})
                                            </p>
                                        @endif
                                    </div>
                                @elseif($item->isRejected())
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Ditolak
                                        </span>
                                        @if($item->reviewer)
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                Oleh: {{ $item->reviewer->name }} ({{ $item->reviewed_at?->translatedFormat('d/m/y H:i') }})
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($item->isPending())
                                    <button 
                                        type="button" 
                                        onclick="openReviewModal({{ $item->id }}, '{{ addslashes($item->intern->name ?? 'Peserta') }}', '{{ $item->isSick() ? 'Sakit' : 'Izin' }}', '{{ $item->start_date->translatedFormat('d M Y') }} s/d {{ $item->end_date->translatedFormat('d M Y') }}', '{{ addslashes($item->reason) }}', '{{ route('admin.leave.review', $item->id) }}')"
                                        class="px-3.5 py-1.5 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                        <span>Verifikasi</span>
                                    </button>
                                @else
                                    <button 
                                        type="button" 
                                        onclick="openDetailModal('{{ addslashes($item->intern->name ?? 'Peserta') }}', '{{ $item->isSick() ? 'Sakit' : 'Izin' }}', '{{ $item->start_date->translatedFormat('d M Y') }} s/d {{ $item->end_date->translatedFormat('d M Y') }}', '{{ $item->getDurationDays() }} Hari', '{{ addslashes($item->reason) }}', '{{ $item->status }}', '{{ addslashes($item->reviewer->name ?? 'Admin') }}', '{{ $item->reviewed_at?->translatedFormat('d F Y, H:i') }} WIB', '{{ addslashes($item->review_notes ?? '-') }}')"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition inline-flex items-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">Tidak ada data permohonan izin/sakit ditemukan</p>
                                    <p class="text-[11px] text-slate-400">Gunakan filter atau kata kunci pencarian yang lain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($leaveRequests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leaveRequests->links() }}
            </div>
        @endif

    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL 1: VERIFIKASI / KEPUTUSAN ADMIN                          -->
<!-- ============================================================== -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white max-w-lg w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-150">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-[#015987]">Verifikasi Pengajuan Izin/Sakit</h4>
                <p id="reviewModalInternInfo" class="text-xs text-slate-500 mt-0.5 font-medium">Memuat info pengajuan...</p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="reviewForm" method="POST" action="" class="p-6 space-y-4">
            @csrf

            <!-- Ringkasan Alasan Pengajuan -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alasan Peserta:</span>
                <p id="reviewModalReason" class="text-xs text-slate-700 italic">"..."</p>
            </div>

            <!-- Keputusan (Disetujui / Ditolak) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Keputusan Persetujuan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    
                    <label class="cursor-pointer">
                        <input type="radio" name="decision" value="approved" id="decisionApproved" checked class="peer sr-only" onchange="toggleNotesRequirement()">
                        <div class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/70 text-center space-y-1 transition hover:border-slate-300">
                            <span class="inline-flex w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <p class="text-xs font-bold text-emerald-800">Setujui Izin</p>
                            <p class="text-[10px] text-slate-500">Pengecualian sah EWS</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="decision" value="rejected" id="decisionRejected" class="peer sr-only" onchange="toggleNotesRequirement()">
                        <div class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/70 text-center space-y-1 transition hover:border-slate-300">
                            <span class="inline-flex w-6 h-6 rounded-full bg-rose-100 text-rose-700 items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <p class="text-xs font-bold text-rose-800">Tolak Permohonan</p>
                            <p class="text-[10px] text-slate-500">Catatan penolakan wajib</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Catatan Keputusan / Alasan Penolakan -->
            <div class="space-y-1.5">
                <label for="reviewNotes" class="block text-xs font-bold text-slate-700">
                    Catatan Review HR <span id="notesRequiredStar" class="text-rose-500 hidden">*</span>
                </label>
                <textarea 
                    id="reviewNotes" 
                    name="review_notes" 
                    rows="3" 
                    placeholder="Contoh: Bukti surat dokter valid / Lampiran dokumen tidak terbaca / dll..."
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] resize-none"
                ></textarea>
                <p id="notesHelpText" class="text-[10px] text-slate-400">Catatan wajib diisi jika permohonan ditolak agar peserta memahami alasannya.</p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Simpan Keputusan
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: DETAIL KEPUTUSAN HISTORIS (READ-ONLY)                  -->
<!-- ============================================================== -->
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-150">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-sm font-bold text-[#015987]">Detail Riwayat Keputusan</h4>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-3.5 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Peserta:</span>
                <span id="detailInternName" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Jenis:</span>
                <span id="detailType" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Periode:</span>
                <span id="detailPeriod" class="font-semibold text-slate-700">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Durasi:</span>
                <span id="detailDuration" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Status Keputusan:</span>
                <span id="detailStatus" class="font-bold">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Diverifikasi Oleh:</span>
                <span id="detailReviewer" class="font-semibold text-slate-700">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-400">Waktu Verifikasi:</span>
                <span id="detailReviewDate" class="text-slate-600">-</span>
            </div>
            <div class="pt-2">
                <span class="block text-slate-400 mb-1">Catatan Verifikasi HR:</span>
                <p id="detailReviewNotes" class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-700 italic">-</p>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 text-right">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-xs transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: PREVIEW BUKTI PRIVAT (GAMBAR / PDF)                    -->
<!-- ============================================================== -->
<div id="evidencePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div>
                <h5 class="text-xs font-bold text-[#015987]" id="previewModalTitle">Bukti Surat / Pendukung</h5>
                <p id="previewModalMeta" class="text-[10px] text-slate-400 mt-0.5 font-medium">-</p>
            </div>
            <div class="flex items-center gap-2">
                <a id="previewModalDownload" href="" target="_blank" class="px-3 py-1 bg-sky-50 text-[#015987] hover:bg-sky-100 text-xs font-semibold rounded-lg border border-sky-200 transition inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Tab Baru</span>
                </a>
                <button type="button" onclick="closeEvidenceModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4 bg-slate-900 flex-1 overflow-auto flex items-center justify-center min-h-[300px]">
            <!-- Preview Gambar -->
            <img id="previewImage" src="" alt="Bukti" class="max-h-[500px] w-auto object-contain rounded-lg hidden">
            
            <!-- Preview Dokumen PDF -->
            <div id="previewPdfContainer" class="w-full h-[500px] hidden">
                <iframe id="previewPdfIframe" src="" class="w-full h-full rounded-lg bg-white border-0"></iframe>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Modal Review
    function openReviewModal(id, internName, type, period, reason, reviewUrl) {
        const modal = document.getElementById('reviewModal');
        const form = document.getElementById('reviewForm');
        const infoEl = document.getElementById('reviewModalInternInfo');
        const reasonEl = document.getElementById('reviewModalReason');
        const notesEl = document.getElementById('reviewNotes');

        form.action = reviewUrl;
        infoEl.innerText = `${internName} • Pengajuan ${type} (${period})`;
        reasonEl.innerText = `"${reason}"`;
        notesEl.value = '';

        document.getElementById('decisionApproved').checked = true;
        toggleNotesRequirement();

        modal.classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function toggleNotesRequirement() {
        const isRejected = document.getElementById('decisionRejected').checked;
        const star = document.getElementById('notesRequiredStar');
        const notes = document.getElementById('reviewNotes');
        const help = document.getElementById('notesHelpText');

        if (isRejected) {
            star.classList.remove('hidden');
            notes.setAttribute('required', 'required');
            help.innerText = 'Wajib memberikan catatan / alasan penolakan untuk peserta.';
            help.className = 'text-[10px] text-rose-500 font-semibold';
        } else {
            star.classList.add('hidden');
            notes.removeAttribute('required');
            help.innerText = 'Catatan review bersifat opsional untuk pengajuan yang disetujui.';
            help.className = 'text-[10px] text-slate-400';
        }
    }

    // Modal Detail Historis
    function openDetailModal(name, type, period, duration, reason, status, reviewer, date, notes) {
        document.getElementById('detailInternName').innerText = name;
        document.getElementById('detailType').innerText = type;
        document.getElementById('detailPeriod').innerText = period;
        document.getElementById('detailDuration').innerText = duration;
        
        const statusEl = document.getElementById('detailStatus');
        if (status === 'approved') {
            statusEl.innerText = 'Disetujui HR';
            statusEl.className = 'font-bold text-emerald-600';
        } else if (status === 'rejected') {
            statusEl.innerText = 'Ditolak';
            statusEl.className = 'font-bold text-rose-600';
        } else {
            statusEl.innerText = 'Menunggu';
            statusEl.className = 'font-bold text-amber-600';
        }

        document.getElementById('detailReviewer').innerText = reviewer;
        document.getElementById('detailReviewDate').innerText = date;
        document.getElementById('detailReviewNotes').innerText = notes || '-';

        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Modal Evidence Preview (Image / PDF)
    function openEvidenceModal(url, filename, filesize, isPdf, internName) {
        const modal = document.getElementById('evidencePreviewModal');
        const titleEl = document.getElementById('previewModalTitle');
        const metaEl = document.getElementById('previewModalMeta');
        const downloadLink = document.getElementById('previewModalDownload');
        const img = document.getElementById('previewImage');
        const pdfContainer = document.getElementById('previewPdfContainer');
        const pdfIframe = document.getElementById('previewPdfIframe');

        titleEl.innerText = `Bukti Izin/Sakit: ${internName}`;
        metaEl.innerText = `${filename} • ${filesize}`;
        downloadLink.href = url;

        if (isPdf) {
            img.classList.add('hidden');
            img.src = '';
            pdfContainer.classList.remove('hidden');
            pdfIframe.src = url;
        } else {
            pdfContainer.classList.add('hidden');
            pdfIframe.src = '';
            img.classList.remove('hidden');
            img.src = url;
        }

        modal.classList.remove('hidden');
    }

    function closeEvidenceModal() {
        const modal = document.getElementById('evidencePreviewModal');
        const img = document.getElementById('previewImage');
        const pdfIframe = document.getElementById('previewPdfIframe');
        img.src = '';
        pdfIframe.src = '';
        modal.classList.add('hidden');
    }

    // Keyboard ESC shortcut
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReviewModal();
            closeDetailModal();
            closeEvidenceModal();
        }
    });
</script>
@endpush
