@php
    $hasSuccess     = session()->has('success') || session()->has('status');
    $successMessage = session('success') ?? session('status');
    $successTitle   = session('success_title') ?? 'Berhasil Ditambahkan';

    // Penyesuaian judul otomatis berdasarkan kata kunci pesan flash jika success_title tidak ditentukan manual
    if (session()->has('success') && !session()->has('success_title')) {
        $msg = strtolower((string) session('success'));
        if (str_contains($msg, 'perbarui') || str_contains($msg, 'update') || str_contains($msg, 'ubah')) {
            $successTitle = 'Berhasil Diperbarui';
        } elseif (str_contains($msg, 'tambah') || str_contains($msg, 'buat') || str_contains($msg, 'create')) {
            $successTitle = 'Berhasil Ditambahkan';
        } elseif (str_contains($msg, 'hapus') || str_contains($msg, 'delete')) {
            $successTitle = 'Berhasil Dihapus';
        } elseif (str_contains($msg, 'aktif') || str_contains($msg, 'nonaktif') || str_contains($msg, 'status')) {
            $successTitle = 'Status Berhasil Diubah';
        } elseif (str_contains($msg, 'simpan') || str_contains($msg, 'save')) {
            $successTitle = 'Berhasil Disimpan';
        }
    }

    $hasError     = session()->has('error');
    $errorMessage = session('error');
    $errorTitle   = session('error_title') ?? 'Terjadi Kesalahan';
@endphp

<!-- Komponen Reusable Modal Pop-up Alert Sukses & Error Persis Sesuai Mockup Figma -->
<div 
    x-data="alertModalApp({
        showSuccess: {{ $hasSuccess ? 'true' : 'false' }},
        successTitle: '{{ addslashes($successTitle) }}',
        successMessage: '{{ addslashes($successMessage ?? '') }}',
        showError: {{ $hasError ? 'true' : 'false' }},
        errorTitle: '{{ addslashes($errorTitle) }}',
        errorMessage: '{{ addslashes($errorMessage ?? '') }}'
    })"
    x-cloak
    @show-success-alert.window="showSuccess($event.detail)"
    @show-error-alert.window="showError($event.detail)"
>
    <!-- ============================================================= -->
    <!-- MODAL POP-UP KONFIRMASI BERHASIL (FIGMA: alert sukses)         -->
    <!-- ============================================================= -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
    >
        <!-- Backdrop Semi-Transparan Gelap Sesuai Mockup Figma -->
        <div 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="close()"
        ></div>

        <!-- Panel Putih Tengah: Rounded Besar & Shadow Tegas -->
        <div
            @click.stop
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90 translate-y-3"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-90 translate-y-3"
            class="relative w-full max-w-sm sm:max-w-[420px] bg-white rounded-3xl sm:rounded-[32px] shadow-2xl border border-slate-100/80 p-8 sm:p-10 text-center z-10"
        >
            <!-- 1. Ikon Centang (Checkmark) dalam Lingkaran Hijau/Teal Lembut Sesuai Figma -->
            <template x-if="type === 'success'">
                <div class="mx-auto mb-6 w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#E4F7F5] flex items-center justify-center flex-shrink-0">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-[#0E8A8A]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>

            <!-- 1b. Ikon Peringatan/Error (Jika Tipe Error) -->
            <template x-if="type === 'error'">
                <div class="mx-auto mb-6 w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </template>

            <!-- 2. Judul Teks Tebal Ukuran Besar (Contoh: "Berhasil Ditambahkan") -->
            <h3 
                class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight"
                x-text="title"
            ></h3>

            <!-- 3. Sub-teks Keterangan Informatif di Bawah Judul -->
            <p 
                class="text-xs sm:text-sm text-slate-500 mt-2.5 leading-relaxed font-normal"
                x-text="message"
            ></p>

            <!-- 4. Tombol Aksi Utama Penuh Berwarna Biru Khas PT WAN ("Tutup") -->
            <button 
                type="button" 
                @click="close()"
                class="w-full mt-7 py-3 sm:py-3.5 px-6 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-sm sm:text-base shadow-sm hover:shadow-md transition-all cursor-pointer active:scale-[0.99]"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function alertModalApp(initial) {
        return {
            isOpen: initial.showSuccess || initial.showError,
            type: initial.showError ? 'error' : 'success',
            title: initial.showError ? initial.errorTitle : initial.successTitle,
            message: initial.showError ? initial.errorMessage : initial.successMessage,

            showSuccess(payload) {
                this.type = 'success';
                this.title = (payload && payload.title) ? payload.title : 'Berhasil Ditambahkan';
                this.message = (payload && payload.message) ? payload.message : (typeof payload === 'string' ? payload : '');
                this.isOpen = true;
            },

            showError(payload) {
                this.type = 'error';
                this.title = (payload && payload.title) ? payload.title : 'Terjadi Kesalahan';
                this.message = (payload && payload.message) ? payload.message : (typeof payload === 'string' ? payload : '');
                this.isOpen = true;
            },

            close() {
                this.isOpen = false;
            },

            init() {
                // Global helper window untuk pemanggilan langsung via JavaScript kapan pun diperlukan:
                // window.showSuccessAlert('Judul', 'Pesan')
                // window.showErrorAlert('Judul', 'Pesan')
                window.showSuccessAlert = (title, message) => {
                    this.showSuccess({ title, message });
                };
                window.showErrorAlert = (title, message) => {
                    this.showError({ title, message });
                };

                // Dukungan keyboard tombol ESC untuk menutup modal alert
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isOpen) {
                        this.close();
                    }
                });
            }
        };
    }
</script>
@endpush
