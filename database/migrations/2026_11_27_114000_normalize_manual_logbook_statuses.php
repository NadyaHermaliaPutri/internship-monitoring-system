<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membersihkan data logbook manual lama agar selaras dengan alur status baru:
     * - Baris Mandiri (target_pic_user_id IS NULL) yang terlanjur berstatus pending dibersihkan ke status = null.
     * - Baris manual yang masih sedang_dikerjakan namun terlanjur pending dibersihkan ke status = null.
     * - Baris manual yang belum memiliki activity_status diisi default yang konsisten.
     */
    public function up(): void
    {
        // 1. Logbook manual Mandiri tidak pernah masuk antrian review PIC
        DB::table('logbooks')
            ->where('source', 'manual')
            ->whereNull('target_pic_user_id')
            ->where('status', 'pending')
            ->update(['status' => null]);

        // 2. Logbook manual yang masih Sedang Dikerjakan belum masuk antrian review PIC
        DB::table('logbooks')
            ->where('source', 'manual')
            ->where('activity_status', 'sedang_dikerjakan')
            ->where('status', 'pending')
            ->update(['status' => null]);

        // 3. Isi activity_status untuk baris manual lama yang masih NULL
        DB::table('logbooks')
            ->where('source', 'manual')
            ->whereNull('activity_status')
            ->whereNull('status')
            ->update(['activity_status' => 'sedang_dikerjakan']);

        DB::table('logbooks')
            ->where('source', 'manual')
            ->whereNull('activity_status')
            ->whereIn('status', ['pending', 'approved', 'revision_requested'])
            ->update(['activity_status' => 'selesai']);
    }

    /**
     * Data cleanup bersifat non-destruktif terhadap skema kolom.
     */
    public function down(): void
    {
        // Tidak ada perubahan skema yang perlu di-rollback.
    }
};
