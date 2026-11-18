@extends('layouts.app')

@section('title', 'Dashboard Monitoring Magang - Peserta')
@section('page-title', 'Dashboard Monitoring Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional')
@section('search-placeholder', 'Cari tugas / aktivitas...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PESERTA MAGANG -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Presensi Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Presensi Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">Hadir</span>
                    <span class="text-xs text-slate-500 font-medium">07:46 WIB</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Tepat Waktu
                </p>
            </div>
        </div>

        <!-- Card 2: Pengajuan Izin / Sakit -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Pengajuan Izin / Sakit</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">1</span>
                    <span class="text-xs text-slate-500 font-medium">Menunggu</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Menunggu Review
                </p>
            </div>
        </div>

        <!-- Card 3: Tugas Menunggu Selesai -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tugas Menunggu Selesai</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">3</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas Aktif</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    1 Mendekati Deadline (Hari ini)
                </p>
            </div>
        </div>

        <!-- Card 4: Logbook Harian -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Logbook Harian</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">Draft</span>
                    <span class="text-xs text-slate-500 font-medium">Tersimpan</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Siap Dikirim Hari Ini
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM KONTEN UTAMA (KIRI 7/12 & KANAN 5/12) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ========================================================== -->
        <!-- KOLOM KIRI (LEBAR 7/12): BANNER PRESENSI & TABEL TUGAS -->
        <!-- ========================================================== -->
        <div class="lg:col-span-7 space-y-8">

            <!-- BANNER 1: PRESENSI MASUK BERHASIL TERCATAT -->
            <div class="bg-[#014467] text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                            Presensi Masuk Berhasil Tercatat
                        </h3>
                    </div>
                    
                    <p class="text-xs text-sky-100">
                        Selfie valid hari ini.
                    </p>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 text-white text-xs font-medium mt-1">
                        <svg class="w-3.5 h-3.5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Waktu Resmi: 09:24:12 WIB</span>
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <button class="px-5 py-2.5 bg-[#0284c7] hover:bg-sky-500 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        Lihat
                    </button>
                </div>
            </div>

            <!-- KARTU 2: TUGAS SAYA & TENGGAT TERDEKAT -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Tugas Saya &amp; Tenggat Terdekat</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar tugas magang aktif dan instruksi penyelesaian PIC</p>
                    </div>
                    <a href="javascript:void(0)" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                        Buka Semua Tugas &rsaquo;
                    </a>
                </div>

                <!-- Tabel Data Tugas -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-6">Judul Tugas</th>
                                <th class="py-4 px-5">PIC Pembimbing</th>
                                <th class="py-4 px-5">Tenggat Waktu</th>
                                <th class="py-4 px-5">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            
                            <!-- Row 1: Slicing UI Dashboard Monitoring -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Slicing UI Dashboard Monitoring</p>
                                        <p class="text-[10px] text-slate-400 mt-1">WANTRACE Web App • Versi 1.0</p>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-medium text-slate-700">
                                    Fadly Husaeni
                                </td>
                                <td class="py-4 px-5 font-semibold text-rose-500">
                                    Hari ini, 17:00
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        Mendekati Deadline
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Kirim Link
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 2: Integrasi Endpoint API Presensi -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Integrasi Endpoint API Presensi</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Backend REST API • ExpressJS</p>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-medium text-slate-700">
                                    Ghani
                                </td>
                                <td class="py-4 px-5 text-slate-600 font-medium">
                                    Besok, 12:00
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        In Progress
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 3: Revisi Form Logbook & Local Draft -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Revisi Form Logbook &amp; Local Draft</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Catatan PIC: Tambahkan state offline</p>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-medium text-slate-700">
                                    Yustyar
                                </td>
                                <td class="py-4 px-5 text-slate-600 font-medium">
                                    5 Nov 2026
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Revisi Masuk (V2)
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Unggah Ulang
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel -->
                <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400">
                    Menampilkan 3 tugas aktif terdekat
                </div>
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- KOLOM KANAN (LEBAR 5/12): STATUS PENGAJUAN IZIN & NOTIFIKASI -->
        <!-- ========================================================== -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- KARTU 1: STATUS PENGAJUAN IZIN -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <!-- Header Kartu -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Status Pengajuan Izin</h3>
                        <p class="text-[11px] text-slate-400">Riwayat permohonan terkini</p>
                    </div>
                    <span class="text-[11px] font-bold text-rose-500 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full">
                        1 Pending
                    </span>
                </div>

                <!-- Box Permohonan Sakit -->
                <div class="p-5 rounded-xl border border-rose-100 bg-rose-50/20 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                            SAKIT (2 HARI)
                        </span>
                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 uppercase">
                            Pending
                        </span>
                    </div>

                    <p class="text-xs text-slate-700 font-medium leading-relaxed">
                        <strong class="text-[#015987]">Demam dan flu berat:</strong> Istirahat sesuai surat anjuran dari dokter klinik.
                    </p>

                    <!-- Lampiran File -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 text-xs text-slate-600 hover:border-slate-300 transition">
                        <span class="flex items-center gap-2 truncate text-[11px]">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Surat_keterangan_dokter.png
                        </span>
                        <svg class="w-4 h-4 text-slate-400 hover:text-slate-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>

                    <p class="text-[11px] text-slate-400">
                        Diajukan: 2 Nov 2026 • Menunggu Verifikasi
                    </p>

                    <button class="w-full py-2.5 border border-[#015987] hover:bg-sky-50 text-[#015987] font-semibold text-xs rounded-xl transition">
                        Lihat Status Lengkap
                    </button>
                </div>
            </div>

            <!-- KARTU 2: NOTIFIKASI TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <h3 class="text-sm font-bold text-[#015987] pb-3 border-b border-slate-100">
                    Notifikasi Terbaru
                </h3>

                <div class="space-y-3.5">
                    
                    <!-- Notif 1 -->
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#015987]">Catatan Baru dari Pembimbing</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">PIC Fadly memberikan feedback pada tugas Slicing Dashboard.</p>
                        </div>
                    </div>

                    <!-- Notif 2 -->
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#015987]">Pengingat Batas Pengumpulan</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">Batas waktu pengumpulan tugas UI tersisa 7 jam 35 menit.</p>
                        </div>
                    </div>

                </div>

                <!-- Footer Notifikasi -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400">Notifikasi terhubung</span>
                    <a href="javascript:void(0)" class="font-semibold text-[#015987] hover:underline text-xs flex items-center gap-1">
                        Semua Notifikasi &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
