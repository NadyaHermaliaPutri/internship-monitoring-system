<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pengaturan batas jam cutoff evaluasi Early Warning System (EWS).
     */
    public function up(): void
    {
        Schema::create('ews_settings', function (Blueprint $table) {
            $table->id();
            $table->string('attendance_cutoff_time', 5)->default('09:00'); // Format HH:MM WIB
            $table->string('logbook_cutoff_time', 5)->default('17:00');    // Format HH:MM WIB
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ews_settings');
    }
};
