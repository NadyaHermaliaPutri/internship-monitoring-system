@extends('layouts.app')

@section('title', 'Detail Tugas')
@section('page-title', 'Detail Tugas Magang')
@section('page-subtitle', 'Informasi penugasan, batas waktu (deadline), dan keterkaitan logbook otomatis')

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
    $isOverdue = $task->isOverdue();
    $statusBadge = match ($task->status->value) {
        'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
        'revision_requested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    @if (session('status'))
        <div class="flex items-center gap-2.5 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border {{ $isOverdue ? 'border-rose-300' : 'border-slate-200/70' }} shadow-xs p-6 sm:p-8 space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                        {{ $task->status->label() }}
                    </span>

                    @if ($isOverdue)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300">
                            OVERDUE (Lewat Deadline)
                        </span>
                    @endif
                </div>

                <h2 class="text-lg font-extrabold text-slate-900">{{ $task->title }}</h2>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span>Peserta: <strong class="text-slate-700">{{ $task->intern?->name }}</strong></span>
                    <span>PIC Pembimbing: <strong class="text-slate-700">{{ $task->pic?->name }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route($rolePrefix . '.tasks.index') }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr; Daftar Tugas
                </a>

                @can('start', $task)
                    <form method="POST" action="{{ route('intern.tasks.start', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-bold text-white shadow-xs transition"
                        >
                            Mulai Kerjakan Tugas
                        </button>
                    </form>
                @endcan

                @can('update', $task)
                    <a
                        href="{{ route('pic.tasks.edit', $task) }}"
                        class="px-4 py-2 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-bold text-white"
                    >
                        Edit Tugas / Deadline
                    </a>
                @endcan
            </div>
        </div>

        {{-- Informasi Waktu --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu Ditugaskan</p>
                <p class="text-xs font-bold text-slate-800 mt-1">
                    {{ $task->assigned_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                </p>
            </div>

            <div class="p-3.5 rounded-xl {{ $isOverdue ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50/70 border-slate-100' }} border">
                <p class="text-[10px] font-bold {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }} uppercase">Batas Waktu (Deadline)</p>
                <p class="text-xs font-bold {{ $isOverdue ? 'text-rose-700' : 'text-[#015987]' }} mt-1">
                    {{ $task->deadline_at ? $task->deadline_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu Diterima (Accepted)</p>
                <p class="text-xs font-bold text-slate-800 mt-1">
                    {{ $task->accepted_at ? $task->accepted_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') . ' WIB' : 'Belum Selesai' }}
                </p>
            </div>
        </div>

        {{-- Deskripsi Tugas --}}
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi &amp; Instruksi Tugas</h3>
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-800 whitespace-pre-line leading-relaxed">{{ $task->description }}</div>
        </div>

        {{-- Info Aksi Peserta (Alur Pengerjaan & Pengumpulan) --}}
        @if ($user->isIntern())
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                <p class="font-bold text-slate-800">Status Pengerjaan Tugas Anda: <span class="text-[#015987]">{{ $task->status->label() }}</span></p>
                @if ($task->status->value === 'assigned')
                    <p class="text-slate-600">
                        Tekan tombol <strong>Mulai Kerjakan Tugas</strong> di atas untuk menandai bahwa Anda telah memulai pengerjaan tugas ini (<code>In Progress</code>).
                    </p>
                @elseif ($task->status->value === 'in_progress')
                    <p class="text-slate-600">
                        Tugas sedang Anda kerjakan. Formulir pengumpulan bukti hasil tugas (URL / video / file) akan tersedia pada tahap pengumpulan tugas.
                    </p>
                @endif
            </div>
        @endif

        {{-- Keterkaitan Logbook Otomatis --}}
        @if ($task->assignmentLogbook)
            <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-[#015987]">Tercatat Otomatis pada Logbook Harian Peserta</p>
                    <p class="text-[11px] text-slate-600">
                        Tanggal Logbook: {{ $task->assignmentLogbook->log_date->translatedFormat('d M Y') }} • {{ $task->assignmentLogbook->title }}
                    </p>
                </div>
                <a
                    href="{{ route($rolePrefix . '.logbooks.show', $task->assignmentLogbook) }}"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-[#015987] hover:bg-sky-50 transition"
                >
                    Lihat Catatan Logbook &rarr;
                </a>
            </div>
        @endif

    </div>

</div>
@endsection
