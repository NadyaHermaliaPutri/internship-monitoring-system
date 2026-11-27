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

        <form method="POST" action="{{ route('intern.logbooks.update', $logbook) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            @include('logbooks.partials.form-fields', ['logbook' => $logbook])

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
