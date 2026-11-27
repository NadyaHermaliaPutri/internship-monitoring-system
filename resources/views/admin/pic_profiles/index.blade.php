@extends('layouts.app')

@section('title', 'Profil PIC Pembimbing')

@section('page-title', 'Profil PIC (Pembimbing)')
@section('page-subtitle', 'PT WAN Teknologi Internasional – Pengelolaan Profil PIC (Pembimbing)')

{{-- Custom Header Right Sesuai Standar WANTRACE --}}
@section('custom-header-right')
    <!-- Widget Tanggal -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/80 shadow-xs">
        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.pic-profiles.index') }}" class="relative hidden sm:block">
        <input 
            type="text" 
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama atau departemen..." 
            class="w-64 pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] shadow-xs transition"
        >
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </form>
@endsection

@section('content')
<div class="space-y-6" x-data="picProfileApp()" x-cloak>

    <!-- Error Validasi Modal -->
    @if($errors->any() && old('_modal') === 'edit')
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs font-semibold text-rose-700 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Terdapat kesalahan pengisian data profil PIC. Silakan periksa kembali.</span>
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- 1. KARTU STATISTIK RINGKASAN PROFIL PIC                           -->
    <!-- ================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <!-- Total PIC -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Total PIC Mentor</span>
                <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_pic'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Mentor</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-sky-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                    <span>Role PIC terdaftar</span>
                </div>
            </div>
        </div>

        <!-- Total Profil PIC -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Profil PIC Terdata</span>
                <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_profiles'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Profil</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-teal-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                    <span>Data pembimbing terdaftar</span>
                </div>
            </div>
        </div>

        <!-- PIC Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">PIC Status Aktif</span>
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['active_pic'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Akun</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-emerald-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    <span>Dapat mengakses sistem</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- 2. TABEL PROFIL PIC (PEMBIMBING)                                  -->
    <!-- ================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- Header Tabel -->
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Daftar Profil PIC (Pembimbing)</h3>
                <p class="text-xs text-slate-500 mt-1">Kelola data akun dan profil pembimbing magang.</p>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6">NAMA PIC</th>
                        <th class="py-4 px-5">Status Akun</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($pics as $index => $pic)
                        @php
                            $profile = $pic->picProfile;
                            $avatarColors = ['bg-sky-100 text-[#015987]', 'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700', 'bg-purple-100 text-purple-700'];
                            $avatarClass = $avatarColors[$index % 4];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- PIC: Nama & Email -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full font-bold text-xs flex items-center justify-center flex-shrink-0 {{ $avatarClass }}">
                                        {{ strtoupper(substr(trim($pic->name), 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs">{{ $pic->name }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $pic->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Akun -->
                            <td class="py-4 px-5">
                                @if($pic->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right">
                                <button 
                                    type="button" 
                                    @click="openEditModal({
                                        id: {{ $pic->id }},
                                        name: '{{ addslashes($pic->name) }}',
                                        email: '{{ addslashes($pic->email) }}'
                                    })"
                                    class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-semibold shadow-2xs transition cursor-pointer"
                                >
                                    Edit Profil
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-12 text-center text-slate-400 text-xs">
                                <svg class="w-9 h-9 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Tidak ada akun PIC yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pics->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    Menampilkan {{ $pics->firstItem() }} - {{ $pics->lastItem() }} dari {{ $pics->total() }} PIC
                </span>
                <div>
                    {{ $pics->links() }}
                </div>
            </div>
        @endif

    </div>

    <!-- ================================================================= -->
    <!-- 3. MODAL POP-UP EDIT PROFIL PIC (ALPINE.JS)                       -->
    <!-- ================================================================= -->
    <div 
        x-show="isEditModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            @click.away="closeModal()" 
            class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-8"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-[#015987] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Edit Profil PIC (Pembimbing)</h4>
                        <p class="text-xs text-slate-400" x-text="editForm.name + ' (' + editForm.email + ')'"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Edit Profil PIC -->
            <form method="POST" :action="editFormAction" class="mt-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">

                <!-- Input Nama PIC -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama PIC <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="editForm.name"
                        placeholder="Nama Lengkap Pembimbing" 
                        required
                        maxlength="150"
                        class="w-full text-xs p-3 bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('name')
                        <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="closeModal()" 
                        class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl border border-slate-200 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-xs font-bold text-white bg-[#015987] hover:bg-[#014467] rounded-xl shadow-xs hover:shadow-md transition cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function picProfileApp() {
        return {
            isEditModalOpen: false,
            editForm: {
                id: null,
                name: '',
                email: ''
            },
            get editFormAction() {
                return `/admin/pic-profiles/${this.editForm.id}`;
            },
            openEditModal(pic) {
                this.editForm.id = pic.id;
                this.editForm.name = pic.name;
                this.editForm.email = pic.email;
                this.isEditModalOpen = true;
            },
            closeModal() {
                this.isEditModalOpen = false;
            }
        };
    }
</script>
@endpush
