@extends('layouts.app')

@section('title', 'Tugas Magang')
@section('page-title', 'Penugasan & Pemantauan Tugas')
@section('page-subtitle', auth()->user()->isPic()
    ? 'Kelola pemberian tugas, batas waktu (deadline), dan pantau progres pengerjaan peserta bimbingan Anda'
    : (auth()->user()->isIntern()
        ? 'Daftar tugas yang diberikan oleh PIC Pembimbing Anda'
        : 'Monitoring seluruh penugasan peserta magang PT WAN Teknologi Internasional'))

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
    $hasActiveFilter = request()->filled('search')
        || request()->filled('status')
        || request()->filled('overdue')
        || request()->filled('intern_id');
@endphp

<div class="space-y-6">

    {{-- Flash Message --}}
    @if (session('status'))
        <div class="flex items-center justify-between gap-3 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    {{-- Ringkasan Metrik Tugas --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Tugas</p>
            <p class="text-2xl font-extrabold text-[#015987] mt-1.5">{{ $summary['total'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Semua status</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-sky-600 uppercase tracking-wider">Aktif Dikerjakan</p>
            <p class="text-2xl font-extrabold text-sky-700 mt-1.5">{{ $summary['assigned'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Ditugaskan / Progres</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Review</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $summary['awaiting_review'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Dikumpulkan peserta</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Perlu Revisi</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1.5">{{ $summary['revision_requested'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu perbaikan</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Diterima (Accepted)</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1.5">{{ $summary['accepted'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Tugas selesai</p>
        </div>

        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Lewat Deadline</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1.5">{{ $summary['overdue'] }}</p>
            <p class="text-[11px] text-rose-400 mt-1">Indikator Overdue</p>
        </div>
    </div>

    {{-- Kontainer Full-Width Daftar Tugas --}}
    <div class="w-full bg-white rounded-2xl border border-slate-200/70 shadow-xs p-5 sm:p-6 space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Daftar Penugasan</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Indikator Overdue dihitung otomatis dari batas waktu (deadline) terbaru sesuai waktu server Asia/Jakarta.
                </p>
            </div>

            @if ($user->isPic())
                <a
                    href="{{ route('pic.tasks.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Buat Tugas Baru</span>
                </a>
            @endif
        </div>

        {{-- Bar Filter & Pencarian --}}
        <form method="GET" action="{{ route($rolePrefix . '.tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 bg-slate-50/70 p-4 rounded-xl border border-slate-100 items-end">
            <div class="{{ $user->isIntern() ? 'lg:col-span-5' : 'lg:col-span-4' }}">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kata Kunci</label>
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul atau deskripsi tugas..."
                        class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987]"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            @if (! $user->isIntern())
                <div class="lg:col-span-3">
                    <label for="intern_id" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Peserta Magang</label>
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

            <div class="{{ $user->isIntern() ? 'lg:col-span-3' : 'lg:col-span-2' }}">
                <label for="status" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Tugas</label>
                <select id="status" name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $statusCase)
                        <option value="{{ $statusCase->value }}" @selected(request('status') === $statusCase->value)>
                            {{ $statusCase->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="{{ $user->isIntern() ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                <label for="overdue" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kondisi Deadline</label>
                <div class="flex items-center gap-2">
                    <select id="overdue" name="overdue" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('overdue') === '1')>Hanya yang Overdue</option>
                    </select>
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#015987] text-white text-xs font-bold hover:bg-[#014467] transition shrink-0">
                        Filter
                    </button>
                    @if ($hasActiveFilter)
                        <a
                            href="{{ route($rolePrefix . '.tasks.index') }}"
                            class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-semibold transition shrink-0"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Daftar Tugas --}}
        @if ($tasks->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 space-y-2">
                <p class="text-sm font-bold text-slate-700">Belum ada data tugas</p>
                <p class="text-xs text-slate-500">
                    @if ($hasActiveFilter)
                        Tidak ada tugas yang cocok dengan filter pencarian Anda.
                    @elseif ($user->isPic())
                        Silakan tekan tombol <strong>+ Buat Tugas Baru</strong> di kanan atas untuk memberikan tugas kepada peserta bimbingan Anda.
                    @elseif ($user->isIntern())
                        Belum ada tugas yang diberikan oleh PIC Pembimbing Anda.
                    @else
                        Belum ada penugasan yang tercatat di sistem.
                    @endif
                </p>
            </div>
        @else
            {{-- TAMPILAN DESKTOP (md ke atas): Tabel Ringkas Full-Width --}}
            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#015987]/5 border-b border-slate-200/80 text-[11px] font-bold text-[#015987] uppercase tracking-wider">
                            <th class="py-3.5 px-4 whitespace-nowrap">Ditugaskan</th>
                            @if (! $user->isIntern())
                                <th class="py-3.5 px-4 whitespace-nowrap">Peserta</th>
                            @else
                                <th class="py-3.5 px-4 whitespace-nowrap">PIC Pembimbing</th>
                            @endif
                            <th class="py-3.5 px-4 w-full">Judul &amp; Deskripsi Ringkas</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Deadline (WIB)</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ($tasks as $taskItem)
                            @php
                                $isOverdue = $taskItem->isOverdue();
                                $needsPicReview = $user->isPic() && $taskItem->canBeReviewedByPic();
                                $statusBadge = match ($taskItem->status->value) {
                                    'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'revision_requested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <tr class="{{ $isOverdue ? 'bg-rose-50/20' : ($needsPicReview ? 'bg-amber-50/35' : '') }} hover:bg-slate-50/80 transition align-middle">
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600 font-medium">
                                    {{ $taskItem->assigned_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-800">
                                    {{ ! $user->isIntern() ? $taskItem->intern?->name : $taskItem->pic?->name }}
                                </td>

                                <td class="py-3.5 px-4 max-w-md">
                                    <p class="font-bold text-slate-900 truncate">{{ $taskItem->title }}</p>
                                    <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $taskItem->description }}</p>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($taskItem->deadline_at)
                                        <span class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-[#015987]' }}">
                                            {{ $taskItem->deadline_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                            {{ $taskItem->status->label() }}
                                        </span>
                                        @if ($isOverdue)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300">
                                                OVERDUE
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        @if ($needsPicReview)
                                            <a
                                                href="{{ route('pic.tasks.show', $taskItem) }}"
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white shadow-xs transition"
                                            >
                                                Review Tugas
                                            </a>
                                        @else
                                            <a
                                                href="{{ route($rolePrefix . '.tasks.show', $taskItem) }}"
                                                class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                            >
                                                Detail
                                            </a>
                                        @endif

                                        @can('start', $taskItem)
                                            <form method="POST" action="{{ route('intern.tasks.start', $taskItem) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                                >
                                                    Mulai Kerjakan
                                                </button>
                                            </form>
                                        @endcan

                                        @can('update', $taskItem)
                                            <a
                                                href="{{ route('pic.tasks.edit', $taskItem) }}"
                                                class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                            >
                                                Edit / Deadline
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN MOBILE / PWA (< md): Daftar Kartu Ringkas 1 Kolom --}}
            <div class="md:hidden space-y-3">
                @foreach ($tasks as $taskItem)
                    @php
                        $isOverdue = $taskItem->isOverdue();
                        $statusBadge = match ($taskItem->status->value) {
                            'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'revision_requested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $isOverdue ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80 bg-white' }} space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                {{ $taskItem->status->label() }}
                            </span>
                            @if ($isOverdue)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300">
                                    OVERDUE
                                </span>
                            @endif
                        </div>

                        <h3 class="text-sm font-bold text-slate-900">{{ $taskItem->title }}</h3>

                        <p class="text-[11px] text-slate-500">
                            {{ ! $user->isIntern() ? 'Peserta: ' . $taskItem->intern?->name : 'PIC: ' . $taskItem->pic?->name }}
                            @if ($taskItem->deadline_at)
                                • Deadline:
                                <strong class="{{ $isOverdue ? 'text-rose-600' : 'text-[#015987]' }}">
                                    {{ $taskItem->deadline_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                                </strong>
                            @endif
                        </p>

                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                            {{ $taskItem->description }}
                        </p>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                            <a
                                href="{{ route($rolePrefix . '.tasks.show', $taskItem) }}"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-[#015987]"
                            >
                                Detail
                            </a>

                            @can('start', $taskItem)
                                <form method="POST" action="{{ route('intern.tasks.start', $taskItem) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#015987] text-xs font-semibold text-white">
                                        Mulai Kerjakan
                                    </button>
                                </form>
                            @endcan

                            @can('update', $taskItem)
                                <a
                                    href="{{ route('pic.tasks.edit', $taskItem) }}"
                                    class="px-3 py-1.5 rounded-xl bg-[#015987] text-xs font-semibold text-white"
                                >
                                    Edit / Deadline
                                </a>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3">
                {{ $tasks->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
