@extends('layouts.app')

@section('title', 'Tambah Logbook Harian')
@section('page-title', 'Tambah Logbook Harian')
@section('page-subtitle', 'Formulir pencatatan aktivitas harian peserta magang')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Formulir Aktivitas Harian</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Anda dapat menambahkan lebih dari 1 aktivitas per hari (maksimal mundur {{ $maxBackdateDays }} hari).
                </p>
            </div>
            <a href="{{ route('intern.logbooks.index') }}" class="text-xs font-bold text-[#015987] hover:underline">
                &larr; Kembali ke Riwayat
            </a>
        </div>

        <form method="POST" action="{{ route('intern.logbooks.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @include('logbooks.partials.form-fields')

            <div class="pt-3 flex items-center justify-end gap-3">
                <a href="{{ route('intern.logbooks.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold shadow-xs transition">
                    Simpan Logbook
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
