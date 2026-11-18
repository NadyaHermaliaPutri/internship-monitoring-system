@extends('layouts.app')

@section('title', 'Dashboard Monitoring Magang - Admin')
@section('page-title', 'Dashboard Monitoring Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional')
@section('search-placeholder', 'Cari peserta...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN (TOP 4 STAT CARDS SESUAI FIGMA) -->
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">14</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    +2 bulan ini
                </p>
            </div>
        </div>

        <!-- Card 2: Presensi Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Presensi Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">12</span>
                    <span class="text-xs text-slate-500 font-medium">Hadir</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    2 pending izin
                </p>
            </div>
        </div>

        <!-- Card 3: Tugas Menunggu Review -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tugas Menunggu Review</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">5</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Perlu ditinjau
                </p>
            </div>
        </div>

        <!-- Card 4: Sinyal Peringatan (EWS) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Sinyal Peringatan (EWS)</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">2</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Perlu segera ditinjau
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM (PRESENSI HARIAN & PENGAJUAN IZIN SAKIT) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (LEBAR 7/12): TABEL PRESENSI HARIAN PESERTA -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
            <!-- Header Kartu -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#015987]">Presensi Harian Peserta</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pembaruan terakhir pukul 09:24 WIB</p>
                </div>
                <a href="javascript:void(0)" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                    Lihat Semua &rsaquo;
                </a>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Nama Peserta</th>
                            <th class="py-4 px-5">Waktu Masuk</th>
                            <th class="py-4 px-5 text-center">Foto Bukti</th>
                            <th class="py-4 px-5">Status</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        
                        <!-- Row 1: Nadya Hermalia (Terlambat) -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        NH
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Nadya Hermalia</p>
                                        <p class="text-[10px] text-slate-400 mt-1">PNP</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-rose-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    08:15
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200">
                                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=64&h=64&fit=crop&crop=face" alt="Foto" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                    <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                    Terlambat
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Detail
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: Atika Naira (Hadir) -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        AN
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Atika Naira</p>
                                        <p class="text-[10px] text-slate-400 mt-1">PNP</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-700">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    08:04
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200">
                                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=64&h=64&fit=crop&crop=face" alt="Foto" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-600 border border-teal-200">
                                    <span class="w-1 h-1 rounded-full bg-teal-500"></span>
                                    Hadir
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Detail
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: Hanifa R. (Hadir) -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        HR
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Hanifa R.</p>
                                        <p class="text-[10px] text-slate-400 mt-1">PNP</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-700">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    07:52
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200">
                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=64&h=64&fit=crop&crop=face" alt="Foto" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-600 border border-teal-200">
                                    <span class="w-1 h-1 rounded-full bg-teal-500"></span>
                                    Hadir
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Detail
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: Mutiara F. R. (Hadir) -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        MF
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Mutiara F. R.</p>
                                        <p class="text-[10px] text-slate-400 mt-1">PNP</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-700">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    07:48
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200">
                                    <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=64&h=64&fit=crop&crop=face" alt="Foto" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-600 border border-teal-200">
                                    <span class="w-1 h-1 rounded-full bg-teal-500"></span>
                                    Hadir
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Detail
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Menampilkan 4 dari 12 kehadiran</span>
                <span class="font-semibold text-sky-600">86% tingkat kehadiran</span>
            </div>
        </div>

        <!-- KOLOM KANAN (LEBAR 5/12): PENGAJUAN IZIN & SAKIT -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
            <!-- Header Kartu -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-[#015987]">Pengajuan Izin &amp; Sakit</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Permintaan terbaru yang perlu ditinjau</p>
                </div>
                <span class="text-[11px] font-semibold text-rose-500 bg-rose-50 px-2.5 py-0.5 rounded-full">
                    2 Menunggu
                </span>
            </div>

            <!-- Item 1: Fira Anindya (Sakit) -->
            <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-600 font-bold text-[10px] flex items-center justify-center">
                            FA
                        </div>
                        <span class="text-xs font-bold text-[#015987]">Fira Anindya</span>
                    </div>
                    <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded">
                        Sakit
                    </span>
                </div>

                <p class="text-xs text-slate-500">
                    Demam dan perlu istirahat
                </p>

                <!-- Tombol Lampiran Dokumen -->
                <div class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 text-xs text-slate-600 cursor-pointer hover:border-slate-300 transition">
                    <span class="flex items-center gap-2 truncate text-[11px]">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Surat keterangan dokter.png
                    </span>
                    <span class="text-slate-400 text-xs">&rsaquo;</span>
                </div>

                <!-- Tombol Aksi: Setujui & Tolak -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button class="w-full py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Setujui
                    </button>
                    <button class="w-full py-2 bg-white hover:bg-rose-50 text-rose-500 border border-rose-200 text-xs font-semibold rounded-xl transition">
                        Tolak
                    </button>
                </div>
            </div>

            <!-- Item 2: Muhammad Iqbal (Izin) -->
            <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-600 font-bold text-[10px] flex items-center justify-center">
                            MI
                        </div>
                        <span class="text-xs font-bold text-[#015987]">Muhammad Iqbal</span>
                    </div>
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded">
                        Izin
                    </span>
                </div>

                <p class="text-xs text-slate-500">
                    Keperluan keluarga mendadak
                </p>

                <!-- Tombol Lampiran Dokumen -->
                <div class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 text-xs text-slate-600 cursor-pointer hover:border-slate-300 transition">
                    <span class="flex items-center gap-2 truncate text-[11px]">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Dokumen pendukung.png
                    </span>
                    <span class="text-slate-400 text-xs">&rsaquo;</span>
                </div>

                <!-- Tombol Aksi: Setujui & Tolak -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button class="w-full py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Setujui
                    </button>
                    <button class="w-full py-2 bg-white hover:bg-rose-50 text-rose-500 border border-rose-200 text-xs font-semibold rounded-xl transition">
                        Tolak
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
