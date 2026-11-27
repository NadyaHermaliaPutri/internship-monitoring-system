<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - WANTRACE | PT WAN Teknologi Internasional</title>

    <!-- Google Fonts: Plus Jakarta Sans & Quando -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Quando&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN Standalone Setup) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        quando: ['Quando', 'serif'],
                    },
                    colors: {
                        wantrace: {
                            blue: '#015987',
                            dark: '#014467',
                            canvas: '#eef4f8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js (Lightweight UI Interactions & Modals) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-quando {
            font-family: 'Quando', serif;
        }
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #eef4f8;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#eef4f8] text-slate-800 antialiased min-h-screen flex overflow-x-hidden">

    <!-- ============================================================== -->
    <!-- SIDEBAR KIRI (NEMPEL FULL SCREEN DI KIRI - WARNA BIRU #015987) -->
    <!-- ============================================================== -->
    <aside class="w-64 bg-[#015987] text-white flex flex-col flex-shrink-0 justify-between py-6 sticky top-0 h-screen z-30">
        
        <div>
            <!-- 1. Header Logo WANTRACE -->
            <div class="flex items-center gap-3 px-6 pt-2 pb-6 border-b border-white/10">
                <img src="{{ asset('images/wi-logo.png') }}" alt="Logo WANTRACE" class="w-9 h-auto object-contain drop-shadow">
                <div>
                    <span class="font-quando text-lg font-bold tracking-wider text-white uppercase block leading-none">WANTRACE</span>
                    <span class="font-quando text-[10px] text-sky-200 tracking-wide block mt-1">Internship System</span>
                </div>
            </div>

            <!-- 2. Profil Singkat Pengguna (Foto Bulat, Status is_active, & Nama) -->
            <div class="px-6 my-7 flex flex-col items-center text-center">
                <div class="relative inline-block">
                    <div class="w-20 h-20 rounded-full ring-4 ring-white/20 shadow-lg overflow-hidden bg-gradient-to-tr from-sky-400 to-indigo-600 flex items-center justify-center text-white font-bold text-xl">
                        {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                    </div>

                    <!-- Titik Indikator Status Database (is_active) -->
                    @if(auth()->user()?->is_active)
                        <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-emerald-500 ring-4 ring-[#015987] shadow-xs" title="Status: Aktif"></span>
                    @else
                        <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-rose-500 ring-4 ring-[#015987] shadow-xs" title="Status: Nonaktif / Selesai"></span>
                    @endif
                </div>

                <h3 class="font-bold text-white text-base mt-3.5 tracking-tight leading-snug">
                    {{ auth()->user()->name ?? 'User' }}
                </h3>
                <p class="text-xs text-sky-200 font-medium mt-0.5">
                    @if(auth()->user()?->role === 'admin')
                        Admin HR
                    @elseif(auth()->user()?->role === 'pic')
                        PIC
                    @else
                        Peserta Magang
                    @endif
                </p>

                <!-- Keterangan Status jika Nonaktif / Selesai Magang -->
                @if(! (auth()->user()?->is_active))
                    <span class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/20 text-rose-200 border border-rose-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                        Nonaktif / Selesai
                    </span>
                @endif
            </div>

            <!-- 3. Navigasi Sidebar (Desain Pinterest Tab Nempel ke Kanan Sesuai Gambar 2) -->
            @php
                $rolePrefix = auth()->user()->role ?? 'admin';
                $isDashboardActive = request()->routeIs('*.dashboard');
                $isLogbookActive = request()->routeIs('*.logbooks.*');
                $isTaskActive = request()->routeIs('*.tasks.*');
                $isAdminCalendarActive = request()->routeIs('admin.calendar.*');
                $isAdminAttendanceActive = request()->routeIs('admin.attendance.*');
                $isAdminLeaveActive = request()->routeIs('admin.leave.*');
                $isInternAttendanceActive = request()->routeIs('intern.attendance.*');
                $isInternLeaveActive = request()->routeIs('intern.leave.*');
            @endphp
            <nav class="mt-6 w-full space-y-1">
                <!-- Menu Dashboard (Semua Role) -->
                <div class="relative w-full pl-5 {{ $isDashboardActive ? 'z-10' : '' }}">
                    <a href="{{ route($rolePrefix . '.dashboard') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 {{ $isDashboardActive ? 'bg-[#eef4f8] text-[#015987] font-bold' : 'text-sky-100 hover:bg-white/10 font-medium' }} text-xs rounded-l-full w-full shadow-none transition">
                        @if($isDashboardActive)
                            <!-- Lekukan Cekung Atas (Top Concave Fillet) -->
                            <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif

                        <!-- Icon 4-Kotak Outline Sesuai Gambar 2 -->
                        <svg class="w-4 h-4 {{ $isDashboardActive ? 'text-[#015987]' : 'text-sky-200' }} flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        </svg>
                        
                        <!-- Teks Dashboard -->
                        <span>Dashboard</span>

                        @if($isDashboardActive)
                            <!-- Lekukan Cekung Bawah (Bottom Concave Fillet) -->
                            <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Menu Logbook Harian (Semua Role) -->
                <div class="relative w-full pl-5 {{ $isLogbookActive ? 'z-10' : '' }}">
                    <a href="{{ route($rolePrefix . '.logbooks.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 {{ $isLogbookActive ? 'bg-[#eef4f8] text-[#015987] font-bold' : 'text-sky-100 hover:bg-white/10 font-medium' }} text-xs rounded-l-full w-full shadow-none transition">
                        @if($isLogbookActive)
                            <!-- Lekukan Cekung Atas (Top Concave Fillet) -->
                            <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif

                        <svg class="w-4 h-4 {{ $isLogbookActive ? 'text-[#015987]' : 'text-sky-200' }} flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>

                        <span>Logbook Harian</span>

                        @if($isLogbookActive)
                            <!-- Lekukan Cekung Bawah (Bottom Concave Fillet) -->
                            <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif
                    </a>
                </div>
                <!-- Menu Tugas Magang (Semua Role) -->
                <div class="relative w-full pl-5 {{ $isTaskActive ? 'z-10' : '' }}">
                    <a href="{{ route($rolePrefix . '.tasks.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 {{ $isTaskActive ? 'bg-[#eef4f8] text-[#015987] font-bold' : 'text-sky-100 hover:bg-white/10 font-medium' }} text-xs rounded-l-full w-full shadow-none transition">
                        @if($isTaskActive)
                            <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif

                        <svg class="w-4 h-4 {{ $isTaskActive ? 'text-[#015987]' : 'text-sky-200' }} flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>

                        <span>Tugas Magang</span>

                        @if($isTaskActive)
                            <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                    <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                </svg>
                            </span>
                        @endif
                    </a>
                </div>

                {{-- ============================================================== --}}
                {{-- MENU KHUSUS ADMINISTRATOR / HR --}}
                {{-- ============================================================== --}}
                @if(auth()->user()?->role === 'admin')
                    <!-- Menu 1: Presensi Peserta (Admin/HR) -->
                    <div class="relative w-full pl-5 {{ $isAdminAttendanceActive ? 'z-10' : '' }}">
                        @if($isAdminAttendanceActive)
                            <a href="{{ route('admin.attendance.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Presensi Peserta</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Presensi Peserta</span>
                            </a>
                        @endif
                    </div>

                    <!-- Menu 2: Kalender Kerja (Admin/HR) -->
                    <div class="relative w-full pl-5 {{ $isAdminCalendarActive ? 'z-10' : '' }}">
                        @if($isAdminCalendarActive)
                            <a href="{{ route('admin.calendar.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Kalender Kerja</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.calendar.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Kalender Kerja</span>
                            </a>
                        @endif
                    </div>

                    <!-- Menu 3: Verifikasi Izin / Sakit (Admin/HR) -->
                    <div class="relative w-full pl-5 {{ $isAdminLeaveActive ? 'z-10' : '' }}">
                        @if($isAdminLeaveActive)
                            <a href="{{ route('admin.leave.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Verifikasi Izin/Sakit</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.leave.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Verifikasi Izin/Sakit</span>
                            </a>
                        @endif
                    </div>

                    <!-- Menu 4: Kelola Akun (Admin/HR) -->
                    @php $isUserManagementActive = request()->routeIs('admin.users.*'); @endphp
                    <div class="relative w-full pl-5 {{ $isUserManagementActive ? 'z-10' : '' }}">
                        @if($isUserManagementActive)
                            <a href="{{ route('admin.users.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Kelola Akun</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Kelola Akun</span>
                            </a>
                        @endif
                    </div>

                    <!-- Menu 5: Profil Peserta (Admin/HR) -->
                    @php $isInternProfileActive = request()->routeIs('admin.intern-profiles.*'); @endphp
                    <div class="relative w-full pl-5 {{ $isInternProfileActive ? 'z-10' : '' }}">
                        @if($isInternProfileActive)
                            <a href="{{ route('admin.intern-profiles.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <span>Profil Peserta</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.intern-profiles.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <span>Profil Peserta</span>
                            </a>
                        @endif
                    </div>
                @endif

                {{-- ============================================================== --}}
                {{-- MENU KHUSUS PESERTA MAGANG (INTERN) --}}
                {{-- ============================================================== --}}
                @if(auth()->user()?->role === 'intern')
                    <!-- Menu: Presensi Masuk (Peserta Magang) -->
                    <div class="relative w-full pl-5 {{ $isInternAttendanceActive ? 'z-10' : '' }}">
                        @if($isInternAttendanceActive)
                            <a href="{{ route('intern.attendance.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Presensi</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('intern.attendance.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Presensi</span>
                            </a>
                        @endif
                    </div>

                    <!-- Menu: Pengajuan Izin (Peserta Magang) -->
                    <div class="relative w-full pl-5 {{ $isInternLeaveActive ? 'z-10' : '' }}">
                        @if($isInternLeaveActive)
                            <a href="{{ route('intern.leave.index') }}" class="relative flex items-center gap-3 py-3 pl-4 pr-6 bg-[#eef4f8] text-[#015987] font-bold text-xs rounded-l-full w-full shadow-none block">
                                <span class="absolute -top-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 0 L20 20 L0 20 A20 20 0 0 0 20 0 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                                <svg class="w-4 h-4 text-[#015987] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Pengajuan Izin</span>
                                <span class="absolute -bottom-5 right-0 w-5 h-5 pointer-events-none">
                                    <svg viewBox="0 0 20 20" fill="none" class="w-full h-full">
                                        <path d="M20 20 L20 0 L0 0 A20 20 0 0 1 20 20 Z" fill="#eef4f8"/>
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('intern.leave.index') }}" class="flex items-center gap-3 py-3 pl-4 pr-6 text-white/80 hover:text-white hover:bg-white/10 font-medium text-xs rounded-l-full w-full transition">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Pengajuan Izin</span>
                            </a>
                        @endif
                    </div>
                @endif
            </nav>
        </div>

        <!-- 4. Tombol Keluar Sistem (Di Bagian Paling Bawah) -->
        <div class="px-6 pt-6 border-t border-white/10 mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2.5 text-xs font-semibold text-rose-300 hover:text-white transition group py-1">
                    <svg class="w-4 h-4 text-rose-300 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- ============================================================== -->
    <!-- AREA KANAN: TOP HEADER & KONTEN UTAMA (FULL SCREEN #eef4f8) -->
    <!-- ============================================================== -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#eef4f8]">
        
        <!-- Top Header Navbar (Menyatu dengan background #eef4f8) -->
        <header class="px-8 pt-7 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4 sticky top-0 z-20 bg-[#eef4f8] border-b border-slate-200/60 shadow-2xs flex-shrink-0">
            <!-- Judul Halaman -->
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        @yield('page-title', 'Dashboard Monitoring Magang')
                    </h1>
                    @yield('page-badge')
                </div>
                <p class="text-xs text-[#015987] font-semibold mt-1.5 leading-relaxed">
                    @yield('page-subtitle', 'PT WAN Teknologi Internasional')
                </p>
            </div>

            <!-- Sisi Kanan: Widget Tanggal, Search Bar, & Tombol Aksi Sesuai Mockup -->
            <div class="flex items-center gap-3 flex-shrink-0">
                @if(View::hasSection('custom-header-right'))
                    @yield('custom-header-right')
                @else
                    <!-- Widget Tanggal -->
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/80 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                <!-- Search Input (Hanya untuk Admin & PIC) -->
                @if(in_array(auth()->user()?->role, ['admin', 'pic'], true))
                    <div class="relative hidden sm:block">
                        <input 
                            type="text" 
                            placeholder="@yield('search-placeholder', 'Cari peserta...')" 
                            class="w-64 pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 shadow-xs transition"
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                @endif

                    @yield('header-actions')

                    <!-- Tombol Notifikasi -->
                    <button class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-600 hover:text-slate-900 shadow-xs transition relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>
                @endif
            </div>
        </header>

        <!-- Konten Utama Halaman -->
        <main class="flex-1 px-8 pb-12 pt-6 overflow-y-auto">
            @yield('content')
        </main>

    </div>

    <!-- Modal Pop-up Alert Sukses & Error Sesuai Figma WANTRACE -->
    @include('partials.alert-modal')

    @stack('scripts')
</body>
</html>
