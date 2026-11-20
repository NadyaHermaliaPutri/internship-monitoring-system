<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai Database Dictionary v1.0 Seksi 3.5 (Tabel attendance - singular).
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_user_id')->constrained('users')->restrictOnDelete();
            $table->date('attendance_date'); // Tanggal lokal Asia/Jakarta
            $table->dateTime('checked_in_at'); // Waktu check-in server (Asia/Jakarta)
            $table->string('status', 20); // on_time atau late
            $table->foreignId('photo_media_id')->constrained('media_files')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Mencegah duplikasi presensi per peserta per tanggal termasuk race condition
            $table->unique(['intern_user_id', 'attendance_date'], 'unique_intern_attendance_date');
            $table->index('attendance_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
