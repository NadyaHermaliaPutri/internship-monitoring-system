@extends('layouts.app')

@section('title', 'Edit Logbook Harian')
@section('page-title', 'Edit Logbook Harian')
@section('page-subtitle', 'Setiap perubahan akan menyimpan salinan riwayat revisi sebelumnya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    @if ($logbook->status?->value === 'revision_requested' && $logbook->review_note)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 space-y-1">
            <p class="text-xs font-bold">Permintaan Revisi dari PIC:</p>
            <p class="text-xs">{{ $logbook->review_note }}</p>
            <p class="text-[11px] text-rose-600 mt-1">
                Setelah Anda menyimpan perbaikan, status logbook akan otomatis kembali menjadi <strong>Menunggu Review</strong>.
            </p>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Perbarui Logbook</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Status saat ini: <strong>{{ $logbook->status?->label() }}</strong>
                </p>
            </div>
            <a href="{{ route('intern.logbooks.show', $logbook) }}" class="text-xs font-bold text-[#015987] hover:underline">
                &larr; Kembali ke Detail
            </a>
        </div>

        <form method="POST" action="{{ route('intern.logbooks.update', $logbook) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="log_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Tanggal Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    id="log_date"
                    name="log_date"
                    value="{{ old('log_date', $logbook->log_date->toDateString()) }}"
                    min="{{ $earliestDate }}"
                    max="{{ $todayDate }}"
                    required
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('log_date') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >
                @error('log_date')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Judul Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $logbook->title) }}"
                    maxlength="150"
                    required
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >
                @error('title')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="activity_description" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Uraian Aktivitas <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="activity_description"
                    name="activity_description"
                    rows="5"
                    required
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('activity_description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >{{ old('activity_description', $logbook->activity_description) }}</textarea>
                @error('activity_description')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="result" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Hasil / Output Kegiatan <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea
                    id="result"
                    name="result"
                    rows="3"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('result') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >{{ old('result', $logbook->result) }}</textarea>
                @error('result')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <a href="{{ route('intern.logbooks.show', $logbook) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
