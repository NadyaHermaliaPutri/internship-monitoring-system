@extends('layouts.app')

@section('title', 'Detail Logbook')
@section('page-title', 'Detail Logbook Harian')
@section('page-subtitle', 'Informasi lengkap aktivitas harian dan jejak revisi')

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    @if (session('status'))
        <div class="flex items-center gap-2.5 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-[#015987]">
                        {{ $logbook->log_date->translatedFormat('l, d F Y') }}
                    </span>

                    @if ($logbook->isTaskAssignment())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            Otomatis Penugasan PIC
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                            Input Manual Peserta
                        </span>
                    @endif

                    @if ($logbook->isManual() && $logbook->status)
                        @php
                            $statusClasses = match ($logbook->status->value) {
                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border-amber-200',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                            {{ $logbook->status->label() }}
                        </span>
                    @endif
                </div>

                <h2 class="text-lg font-extrabold text-slate-900">{{ $logbook->title }}</h2>
                <p class="text-xs text-slate-500">
                    Peserta: <strong class="text-slate-700">{{ $logbook->intern?->name }}</strong> ({{ $logbook->intern?->email }})
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route($rolePrefix . '.logbooks.index') }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr; Riwayat
                </a>

                @can('update', $logbook)
                    <a
                        href="{{ route('intern.logbooks.edit', $logbook) }}"
                        class="px-4 py-2 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-bold text-white"
                    >
                        Edit Logbook
                    </a>
                @endcan
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Aktivitas</h3>
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-800 whitespace-pre-line leading-relaxed">{{ $logbook->activity_description }}</div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Hasil / Output Kegiatan</h3>
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-700 whitespace-pre-line leading-relaxed">{{ $logbook->result ?: 'Tidak ada catatan hasil tambahan.' }}</div>
            </div>

            @if ($logbook->task_id)
                <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-[#015987]">Terhubung dengan Penugasan PIC</p>
                        <p class="text-[11px] text-slate-600 mt-0.5">
                            Catatan ini dibuat otomatis oleh sistem saat PIC memberikan tugas (bukan penanda tugas selesai).
                        </p>
                    </div>
                    <a
                        href="{{ route($rolePrefix . '.tasks.show', $logbook->task_id) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-[#015987] hover:bg-sky-50 transition"
                    >
                        Lihat Detail Tugas &rarr;
                    </a>
                </div>
            @endif

            @if ($logbook->reviewed_by || $logbook->review_note)
                <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 space-y-1 text-xs">
                    <p class="font-bold text-[#015987]">
                        Tinjauan PIC {{ $logbook->reviewer ? '(' . $logbook->reviewer->name . ')' : '' }}
                        @if ($logbook->reviewed_at)
                            <span class="font-normal text-slate-500">• {{ $logbook->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</span>
                        @endif
                    </p>
                    <p class="text-slate-700">{{ $logbook->review_note ?: 'Tidak ada catatan.' }}</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Riwayat Revisi --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-[#015987]">Riwayat Perubahan / Revisi ({{ $logbook->revisions->count() }})</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Setiap perubahan pada logbook disimpan sebagai snapshot dan tidak menimpa catatan sebelumnya.
            </p>
        </div>

        @if ($logbook->revisions->isEmpty())
            <p class="text-xs text-slate-500 py-4">Belum ada riwayat perubahan pada logbook ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($logbook->revisions as $revision)
                    <div class="p-4 rounded-xl bg-slate-50/60 border border-slate-200/70 space-y-2 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold text-[#015987]">
                                Revisi #{{ $revision->revision_no }} — Versi Sebelum Perubahan
                            </span>
                            <span class="text-[11px] text-slate-500">
                                Diubah pada {{ $revision->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                                oleh {{ $revision->editor?->name ?? 'Peserta' }}
                            </span>
                        </div>

                        <p class="text-slate-800 font-semibold">
                            [{{ $revision->log_date->translatedFormat('d M Y') }}] {{ $revision->title }}
                        </p>
                        <p class="text-slate-600 whitespace-pre-line">{{ $revision->activity_description }}</p>

                        @if ($revision->result)
                            <p class="text-[11px] text-slate-500"><strong>Hasil sebelumnya:</strong> {{ $revision->result }}</p>
                        @endif

                        @if ($revision->review_note_before)
                            <p class="text-[11px] text-rose-600">
                                <strong>Catatan PIC saat itu ({{ $revision->status_before }}):</strong> {{ $revision->review_note_before }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
