@extends('layouts.app')

@section('title', 'Daftar Notifikasi')
@section('page-title', 'Pusat Notifikasi')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Riwayat Pemberitahuan Presensi & Izin/Sakit')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-[#015987]">Semua Notifikasi Anda</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $unreadCount > 0 ? "{$unreadCount} notifikasi belum dibaca" : 'Semua notifikasi sudah dibaca' }}
                </p>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        @if($notifications->isEmpty())
            <div class="p-12 text-center space-y-2">
                <p class="text-xs font-bold text-slate-600">Belum Ada Notifikasi</p>
                <p class="text-[11px] text-slate-400">Pemberitahuan terkait presensi dan pengajuan izin/sakit akan muncul di sini.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($notifications as $notif)
                    @php
                        $data = $notif->data ?? [];
                        $isUnread = is_null($notif->read_at);
                        $type = $data['type'] ?? 'info';
                        $iconBg = match ($type) {
                            'danger' => 'bg-rose-50 text-rose-500',
                            'warning' => 'bg-amber-50 text-amber-600',
                            default => 'bg-sky-50 text-[#015987]',
                        };
                    @endphp
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-sky-50/40' : 'bg-white' }} hover:bg-slate-50/70 transition">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <span class="w-9 h-9 rounded-xl {{ $iconBg }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    @if($type === 'danger')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    @elseif($type === 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    @endif
                                </svg>
                            </span>
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-[#015987]">{{ $data['title'] ?? 'Notifikasi' }}</h4>
                                    @if($isUnread)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#015987] text-white">Baru</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $data['message'] ?? '' }}</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ $notif->created_at->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i') }} WIB
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold {{ $isUnread ? 'bg-[#015987] hover:bg-[#01486e] text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} transition">
                                    {{ !empty($data['action_url']) ? 'Buka & Tandai Dibaca' : 'Tandai Dibaca' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
