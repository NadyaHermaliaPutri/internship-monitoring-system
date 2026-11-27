@extends('layouts.app')

@section('title', 'Logbook Harian')
@section('page-title', 'Logbook Aktivitas Harian')
@section('page-subtitle', auth()->user()->isIntern()
    ? 'Catat aktivitas harian Anda dan pantau status verifikasi dari PIC Pembimbing'
    : (auth()->user()->isPic()
        ? 'Pantau logbook harian peserta magang yang berada dalam cakupan penugasan Anda'
        : 'Monitoring seluruh logbook harian peserta magang PT WAN Teknologi Internasional'))

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
    $hasActiveFilter = request()->filled('search')
        || request()->filled('source')
        || request()->filled('status')
        || request()->filled('date')
        || request()->filled('intern_id');
@endphp

<div class="space-y-6">

    {{-- 4 Kartu Ringkasan KPI Logbook (Terhubung ke Filter Status) --}}
    @php
        $activeStatusFilter = request('status');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a
            href="{{ route($rolePrefix . '.logbooks.index') }}"
            class="block bg-white rounded-2xl border {{ empty($activeStatusFilter) ? 'border-[#015987] ring-2 ring-[#015987]/10' : 'border-slate-200/70 hover:border-sky-300' }} p-4 shadow-xs transition"
        >
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Logbook Manual</p>
            <p class="text-2xl font-extrabold text-[#015987] mt-1.5">{{ $summary['total_manual'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Diisi oleh peserta</p>
        </a>

        <a
            href="{{ route($rolePrefix . '.logbooks.index', ['status' => 'pending']) }}"
            class="block bg-white rounded-2xl border {{ $activeStatusFilter === 'pending' ? 'border-amber-500 ring-2 ring-amber-500/15' : 'border-slate-200/70 hover:border-amber-300' }} p-4 shadow-xs transition"
        >
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Review</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $summary['pending'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Klik untuk filter Pending</p>
        </a>

        <a
            href="{{ route($rolePrefix . '.logbooks.index', ['status' => 'approved']) }}"
            class="block bg-white rounded-2xl border {{ $activeStatusFilter === 'approved' ? 'border-emerald-500 ring-2 ring-emerald-500/15' : 'border-slate-200/70 hover:border-emerald-300' }} p-4 shadow-xs transition"
        >
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Disetujui</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1.5">{{ $summary['approved'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Klik untuk filter Disetujui</p>
        </a>

        <a
            href="{{ route($rolePrefix . '.logbooks.index', ['status' => 'revision_requested']) }}"
            class="block bg-white rounded-2xl border {{ $activeStatusFilter === 'revision_requested' ? 'border-rose-500 ring-2 ring-rose-500/15' : 'border-slate-200/70 hover:border-rose-300' }} p-4 shadow-xs transition"
        >
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Perlu Revisi</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1.5">{{ $summary['revision_requested'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Klik untuk filter Revisi</p>
        </a>
    </div>

    {{-- Kontainer Full-Width: Header Aksi, Bar Filter, Tabel Desktop & Kartu Mobile --}}
    <div class="w-full bg-white rounded-2xl border border-slate-200/70 shadow-xs p-5 sm:p-6 space-y-5">

        {{-- Header Daftar Riwayat + Tombol + Tambah Logbook --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Riwayat Logbook Harian</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan catatan aktivitas manual peserta dan catatan otomatis penugasan PIC.
                </p>
            </div>

            @can('create', \App\Models\Logbook::class)
                <button
                    type="button"
                    id="open-create-logbook-modal"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Logbook</span>
                </button>
            @endcan
        </div>

        {{-- Bar Filter & Pencarian Kata Kunci --}}
        <form method="GET" action="{{ route($rolePrefix . '.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 bg-slate-50/70 p-4 rounded-xl border border-slate-100 items-end">
            {{-- Pencarian Kata Kunci --}}
            <div class="{{ $user->isIntern() ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kata Kunci</label>
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kegiatan..."
                        class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987]"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Filter Peserta (Khusus PIC & Admin) --}}
            @if (! $user->isIntern())
                <div class="lg:col-span-2">
                    <label for="intern_id" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Peserta</label>
                    <select id="intern_id" name="intern_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua Peserta</option>
                        @foreach ($internOptions as $internOption)
                            <option value="{{ $internOption->id }}" @selected((string) request('intern_id') === (string) $internOption->id)>
                                {{ $internOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Filter Sumber --}}
            <div class="lg:col-span-2">
                <label for="source" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sumber</label>
                <select id="source" name="source" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                    <option value="">Semua Sumber</option>
                    <option value="manual" @selected(request('source') === 'manual')>Aktivitas Mandiri</option>
                    <option value="task_assignment" @selected(request('source') === 'task_assignment')>Penugasan PIC</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="lg:col-span-2">
                <label for="status" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="revision_requested" @selected(request('status') === 'revision_requested')>Perlu Revisi</option>
                </select>
            </div>

            {{-- Filter Tanggal & Tombol Aksi --}}
            <div class="{{ $user->isIntern() ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                <label for="date" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ request('date') }}"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700"
                    >
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#015987] text-white text-xs font-bold hover:bg-[#014467] transition shrink-0">
                        Filter
                    </button>
                    @if ($hasActiveFilter)
                        <a
                            href="{{ route($rolePrefix . '.logbooks.index') }}"
                            class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-semibold transition shrink-0"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Daftar Riwayat Logbook --}}
        @if ($user->isIntern())
            {{-- TAMPILAN PESERTA: Pengelompokan Per Hari + Dual-Pagination (Per-Hari & Antar-Hari) --}}
            @if ($dailyGroups->isEmpty())
                <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 space-y-3">
                    <p class="text-sm font-bold text-slate-700">Belum ada catatan logbook</p>
                    <p class="text-xs text-slate-500">
                        @if ($hasActiveFilter)
                            Tidak ada catatan logbook yang cocok dengan filter pencarian Anda.
                        @else
                            Silakan catat aktivitas harian Anda dengan menekan tombol <strong>+ Tambah Logbook</strong> di kanan atas.
                        @endif
                    </p>
                    @if ($hasActiveFilter)
                        <div>
                            <a
                                href="{{ route('intern.logbooks.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-semibold text-slate-700 transition"
                            >
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($dailyGroups as $group)
                        @php
                            $dailyLogs = $group['logs'];
                        @endphp
                        <div
                            data-logbook-day="{{ $group['date'] }}"
                            class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-xs"
                        >
                            {{-- Header Hari --}}
                            <div class="flex flex-wrap items-center justify-between gap-2.5 px-5 py-3.5 bg-[#015987]/5 border-b border-slate-200/80">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-[#015987] text-white flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-extrabold text-[#015987]">
                                            {{ $group['carbon']->translatedFormat('l, d F Y') }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500">
                                            @if ($group['manual_count'] > 0 && $group['task_count'] > 0)
                                                {{ $group['manual_count'] }} Aktivitas Mandiri &bull; {{ $group['task_count'] }} Penugasan PIC
                                            @elseif ($group['task_count'] > 0)
                                                {{ $group['task_count'] }} Penugasan PIC
                                            @else
                                                {{ $group['manual_count'] }} Aktivitas Mandiri
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white text-[#015987] border border-sky-200">
                                    {{ $dailyLogs->count() }} Catatan
                                </span>
                            </div>

                            {{-- Tabel Desktop Per Hari (tanpa pengulangan kolom Tanggal & tanpa kolom Sumber terpisah) --}}
                            <div class="hidden md:block overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-[#015987] uppercase tracking-wider text-center">
                                            <th class="py-3 px-4 w-full text-center">Kegiatan &amp; Hasil/Bukti</th>
                                            <th class="py-3 px-4 whitespace-nowrap text-center">Status</th>
                                            <th class="py-3 px-4 whitespace-nowrap text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs">
                                        @foreach ($dailyLogs as $item)
                                            <tr class="hover:bg-slate-50/80 transition align-middle">
                                                {{-- Kegiatan & Hasil/Bukti --}}
                                                <td class="py-3.5 px-4 max-w-xl">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-bold text-slate-900">{{ $item->title }}</span>
                                                        @if ($item->isTaskAssignment())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 shrink-0">
                                                                Otomatis Tugas
                                                            </span>
                                                        @elseif ($item->targetPic)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                                                PIC: {{ $item->targetPic->name }}
                                                            </span>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                                                Mandiri
                                                            </span>
                                                        @endif
                                                        @if ($item->revisions_count > 0)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                                                {{ $item->revisions_count }}x Revisi
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if (filled($item->activity_description) && trim($item->activity_description) !== trim($item->title))
                                                        <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                                            {{ $item->activity_description }}
                                                        </p>
                                                    @endif
                                                    @if (filled($item->result))
                                                        <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5">
                                                            <span class="font-semibold text-slate-500">Hasil:</span> {{ $item->result }}
                                                        </p>
                                                    @endif
                                                    @if ($item->evidenceMedia)
                                                        <div class="mt-1">
                                                            <a
                                                                href="{{ route('media.private.show', $item->evidenceMedia) }}"
                                                                target="_blank"
                                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#015987] hover:underline"
                                                            >
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                                <span>Lihat Screenshot Bukti</span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Status (1 badge utama yang bersih) --}}
                                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                                    @php $statusBadge = $item->displayStatusBadge(); @endphp
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['classes'] }}"
                                                        @if ($statusBadge['title']) title="{{ $statusBadge['title'] }}" @endif
                                                    >
                                                        {{ $statusBadge['label'] }}
                                                    </span>
                                                </td>

                                                {{-- Aksi --}}
                                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                                    <div class="inline-flex items-center justify-center gap-2">
                                                        @if ($item->isTaskAssignment() && $item->task_id)
                                                            <a
                                                                href="{{ route('intern.tasks.show', $item->task_id) }}"
                                                                class="px-3.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-xs font-semibold text-[#015987] transition"
                                                            >
                                                                Lihat Tugas
                                                            </a>
                                                        @else
                                                            <a
                                                                href="{{ route('intern.logbooks.show', $item) }}"
                                                                class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                                            >
                                                                Detail
                                                            </a>

                                                            @can('complete', $item)
                                                                <form method="POST" action="{{ route('intern.logbooks.complete', $item) }}" class="inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button
                                                                        type="submit"
                                                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white transition cursor-pointer"
                                                                    >
                                                                        Selesai
                                                                    </button>
                                                                </form>
                                                            @endcan

                                                            @can('update', $item)
                                                                <a
                                                                    href="{{ route('intern.logbooks.edit', $item) }}"
                                                                    class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                                                >
                                                                    Edit
                                                                </a>
                                                            @endcan
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tampilan Mobile Per Hari (< md) --}}
                            <div class="md:hidden divide-y divide-slate-100">
                                @foreach ($dailyLogs as $item)
                                    <div class="p-4 space-y-2.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @if ($item->isTaskAssignment())
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                        Otomatis Tugas
                                                    </span>
                                                @elseif ($item->targetPic)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        PIC: {{ $item->targetPic->name }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                        Mandiri
                                                    </span>
                                                @endif

                                                @if ($item->revisions_count > 0)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        {{ $item->revisions_count }}x Revisi
                                                    </span>
                                                @endif
                                            </div>

                                            @php $statusBadge = $item->displayStatusBadge(); @endphp
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['classes'] }}"
                                                @if ($statusBadge['title']) title="{{ $statusBadge['title'] }}" @endif
                                            >
                                                {{ $statusBadge['label'] }}
                                            </span>
                                        </div>

                                        <h4 class="text-sm font-bold text-slate-900">{{ $item->title }}</h4>

                                        @if (filled($item->activity_description) && trim($item->activity_description) !== trim($item->title))
                                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                                {{ $item->activity_description }}
                                            </p>
                                        @endif

                                        @if (filled($item->result))
                                            <p class="text-xs text-slate-600 line-clamp-2">
                                                <span class="font-semibold text-slate-500">Hasil:</span> {{ $item->result }}
                                            </p>
                                        @endif

                                        @if ($item->evidenceMedia)
                                            <div>
                                                <a
                                                    href="{{ route('media.private.show', $item->evidenceMedia) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-[#015987] hover:underline"
                                                >
                                                    <span>Lihat Screenshot Bukti &rarr;</span>
                                                </a>
                                            </div>
                                        @endif

                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                                            @if ($item->isTaskAssignment() && $item->task_id)
                                                <a
                                                    href="{{ route('intern.tasks.show', $item->task_id) }}"
                                                    class="px-3.5 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-xs font-semibold text-[#015987]"
                                                >
                                                    Lihat Tugas
                                                </a>
                                            @else
                                                <a
                                                    href="{{ route('intern.logbooks.show', $item) }}"
                                                    class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-[#015987]"
                                                >
                                                    Detail
                                                </a>

                                                @can('complete', $item)
                                                    <form method="POST" action="{{ route('intern.logbooks.complete', $item) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button
                                                            type="submit"
                                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 text-xs font-semibold text-white cursor-pointer"
                                                        >
                                                            Selesai
                                                        </button>
                                                    </form>
                                                @endcan

                                                @can('update', $item)
                                                    <a
                                                        href="{{ route('intern.logbooks.edit', $item) }}"
                                                        class="px-3 py-1.5 rounded-xl bg-[#015987] text-xs font-semibold text-white"
                                                    >
                                                        Edit
                                                    </a>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Utama Antar-Hari: Hanya tampil saat > 1 halaman --}}
                @if ($paginatedDates && $paginatedDates->hasPages())
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-xs font-medium text-slate-500">
                            Hari {{ $paginatedDates->firstItem() }}&ndash;{{ $paginatedDates->lastItem() }} dari {{ $paginatedDates->total() }}
                        </p>
                        <div>
                            {{ $paginatedDates->links() }}
                        </div>
                    </div>
                @endif
            @endif

            @if ($dailyGroups->isEmpty() && $paginatedDates && $paginatedDates->hasPages())
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs font-medium text-slate-500">
                        Total {{ $paginatedDates->total() }} hari
                    </p>
                    <div>
                        {{ $paginatedDates->links() }}
                    </div>
                </div>
            @endif
        @elseif ($logbooks->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 space-y-2">
                <p class="text-sm font-bold text-slate-700">Belum ada catatan logbook</p>
                <p class="text-xs text-slate-500">
                    @if ($hasActiveFilter)
                        Tidak ada catatan logbook yang cocok dengan filter pencarian Anda.
                    @else
                        Belum ada logbook peserta dalam cakupan penugasan Anda.
                    @endif
                </p>
            </div>
        @else
            {{-- TAMPILAN DESKTOP (md ke atas): Tabel Ringkas Full-Width --}}
            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#015987]/5 border-b border-slate-200/80 text-[11px] font-bold text-[#015987] uppercase tracking-wider text-center">
                            <th class="py-3.5 px-4 whitespace-nowrap text-center">Tanggal</th>
                            @if (! $user->isIntern())
                                <th class="py-3.5 px-4 whitespace-nowrap text-center">Peserta</th>
                            @endif
                            <th class="py-3.5 px-4 w-full text-center">Kegiatan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center">Status</th>
                            <th class="py-3.5 px-4 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ($logbooks as $item)
                            @php
                                $needsPicReview = $user->isPic() && $user->can('review', $item);
                            @endphp
                            <tr class="{{ $needsPicReview ? 'bg-amber-50/35' : '' }} hover:bg-slate-50/80 transition align-middle">
                                {{-- Tanggal --}}
                                <td class="py-3.5 px-4 whitespace-nowrap font-bold text-[#015987] text-center">
                                    {{ $item->log_date->translatedFormat('d M Y') }}
                                </td>

                                {{-- Peserta (PIC & Admin) --}}
                                @if (! $user->isIntern())
                                    <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-800 text-center">
                                        {{ $item->intern?->name }}
                                    </td>
                                @endif

                                {{-- Kegiatan --}}
                                <td class="py-3.5 px-4 max-w-md">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ $item->title }}</span>
                                        @if ($item->isTaskAssignment())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 shrink-0">
                                                Otomatis Tugas
                                            </span>
                                        @elseif ($item->targetPic)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                                PIC: {{ $item->targetPic->name }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                                Mandiri
                                            </span>
                                        @endif
                                        @if ($item->revisions_count > 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                                {{ $item->revisions_count }}x Revisi
                                            </span>
                                        @endif
                                    </div>
                                    @if (filled($item->activity_description) && trim($item->activity_description) !== trim($item->title))
                                        <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                            {{ $item->activity_description }}
                                        </p>
                                    @endif
                                    @if ($item->evidenceMedia)
                                        <div class="mt-1">
                                            <a
                                                href="{{ route('media.private.show', $item->evidenceMedia) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#015987] hover:underline"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Lihat Screenshot Bukti</span>
                                            </a>
                                        </div>
                                    @endif
                                    @if ($item->relatedTask)
                                        <p class="text-[10px] text-[#015987] font-semibold truncate mt-0.5">
                                            Tugas: {{ $item->relatedTask->title }} (PIC: {{ $item->relatedTask->pic?->name ?? 'PIC' }})
                                        </p>
                                    @endif
                                </td>

                                {{-- Status (1 badge utama yang bersih) --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                    @php $statusBadge = $item->displayStatusBadge(); @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['classes'] }}"
                                        @if ($statusBadge['title']) title="{{ $statusBadge['title'] }}" @endif
                                    >
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        @if ($item->isTaskAssignment() && $item->task_id)
                                            <a
                                                href="{{ route($rolePrefix . '.tasks.show', $item->task_id) }}"
                                                class="px-3.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-xs font-semibold text-[#015987] transition"
                                            >
                                                Lihat Tugas
                                            </a>
                                        @elseif ($needsPicReview)
                                            <a
                                                href="{{ route('pic.logbooks.show', $item) }}"
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white shadow-xs transition"
                                            >
                                                Review Logbook
                                            </a>
                                        @else
                                            <a
                                                href="{{ route($rolePrefix . '.logbooks.show', $item) }}"
                                                class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                            >
                                                Detail
                                            </a>
                                        @endif

                                        @can('update', $item)
                                            <a
                                                href="{{ route('intern.logbooks.edit', $item) }}"
                                                class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN MOBILE / PWA (< md): Daftar Kartu Ringkas 1 Kolom dengan line-clamp-2 --}}
            <div class="md:hidden space-y-3">
                @foreach ($logbooks as $item)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-white space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-[#015987]">
                                {{ $item->log_date->translatedFormat('d M Y') }}
                                @if (! $user->isIntern())
                                    <span class="text-slate-600 font-semibold">• {{ $item->intern?->name }}</span>
                                @endif
                            </span>

                            @php $statusBadge = $item->displayStatusBadge(); @endphp
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['classes'] }}"
                                @if ($statusBadge['title']) title="{{ $statusBadge['title'] }}" @endif
                            >
                                {{ $statusBadge['label'] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            @if ($item->isTaskAssignment())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    Otomatis Tugas
                                </span>
                            @elseif ($item->targetPic)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    PIC: {{ $item->targetPic->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    Mandiri
                                </span>
                            @endif

                            @if ($item->revisions_count > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $item->revisions_count }}x Revisi
                                </span>
                            @endif
                        </div>

                        <h3 class="text-sm font-bold text-slate-900">{{ $item->title }}</h3>

                        @if (filled($item->activity_description) && trim($item->activity_description) !== trim($item->title))
                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                {{ $item->activity_description }}
                            </p>
                        @endif

                        @if ($item->evidenceMedia)
                            <div>
                                <a
                                    href="{{ route('media.private.show', $item->evidenceMedia) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-[#015987] hover:underline"
                                >
                                    <span>Lihat Screenshot Bukti &rarr;</span>
                                </a>
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                            @if ($item->isTaskAssignment() && $item->task_id)
                                <a
                                    href="{{ route($rolePrefix . '.tasks.show', $item->task_id) }}"
                                    class="px-3.5 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-xs font-semibold text-[#015987]"
                                >
                                    Lihat Tugas
                                </a>
                            @else
                                <a
                                    href="{{ route($rolePrefix . '.logbooks.show', $item) }}"
                                    class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-[#015987]"
                                >
                                    Detail
                                </a>

                                @can('update', $item)
                                    <a
                                        href="{{ route('intern.logbooks.edit', $item) }}"
                                        class="px-3 py-1.5 rounded-xl bg-[#015987] text-xs font-semibold text-white"
                                    >
                                        Edit
                                    </a>
                                @endcan
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3">
                {{ $logbooks->links() }}
            </div>
        @endif

    </div>

</div>

{{-- Modal Popup Tambah Logbook Harian (Khusus Peserta) --}}
@can('create', \App\Models\Logbook::class)
    @php
        $shouldOpenCreateModal = $errors->any() && old('_modal') === 'create';
    @endphp

    <div
        id="create-logbook-modal"
        data-open-on-load="{{ $shouldOpenCreateModal ? 'true' : 'false' }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-logbook-modal-title"
        class="{{ $shouldOpenCreateModal ? 'flex' : 'hidden' }} fixed inset-0 z-[100] items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
    >
        <div class="relative z-10 bg-white w-full max-w-2xl rounded-2xl border border-slate-200 shadow-xl max-h-[90vh] flex flex-col overflow-hidden">
            {{-- Header Modal (Tetap di atas, tidak menimpa body saat di-scroll) --}}
            <div class="shrink-0 flex items-start justify-between gap-4 px-6 py-4 bg-white border-b border-slate-100">
                <div>
                    <h3 id="create-logbook-modal-title" class="text-base font-bold text-[#015987]">Tambah Logbook Harian</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Anda dapat menambahkan lebih dari 1 kegiatan per hari (maksimal mundur {{ $maxBackdateDays }} hari).
                    </p>
                </div>
                <button
                    type="button"
                    data-close-create-modal
                    aria-label="Tutup modal"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form membungkus penuh area body scroll dan footer tombol submit --}}
            <form
                id="create-logbook-form"
                method="POST"
                action="{{ route('intern.logbooks.store') }}"
                enctype="multipart/form-data"
                class="flex flex-col flex-1 min-h-0"
            >
                @csrf
                <input type="hidden" name="_modal" value="create">

                <div id="create-logbook-modal-body" class="flex-1 overflow-y-auto px-6 pt-5 pb-6 space-y-5">
                    @include('logbooks.partials.form-fields', ['prefix' => 'modal_create_'])
                </div>

                <div class="shrink-0 px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        data-close-create-modal
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="create-logbook-form"
                        class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold shadow-sm transition cursor-pointer"
                    >
                        Simpan Logbook
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('create-logbook-modal');
            const modalBody = document.getElementById('create-logbook-modal-body');
            const openBtn = document.getElementById('open-create-logbook-modal');
            if (!modal) return;

            const closeBtns = modal.querySelectorAll('[data-close-create-modal]');
            const focusTarget = document.getElementById('modal_create_title');

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
                if (modalBody) {
                    modalBody.scrollTop = 0;
                }
                if (focusTarget) {
                    setTimeout(() => {
                        focusTarget.focus({ preventScroll: true });
                    }, 50);
                }
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            if (openBtn) {
                openBtn.addEventListener('click', openModal);
            }

            closeBtns.forEach((btn) => btn.addEventListener('click', closeModal));

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            if (modal.dataset.openOnLoad === 'true') {
                openModal();
            }
        })();
    </script>
@endcan
@endsection
