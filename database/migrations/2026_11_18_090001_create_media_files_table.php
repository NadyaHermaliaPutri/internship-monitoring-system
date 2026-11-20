<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai Database Dictionary v1.0 Seksi 3.12 (Tabel media_files).
     */
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('category', 30); // attendance_photo, leave_evidence, task_evidence
            $table->string('disk', 50)->default('local_private');
            $table->string('file_path', 500); // Path relatif private disk; bukan URL publik
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size_bytes');
            $table->dateTime('uploaded_at');
            $table->timestamps();

            $table->index('owner_user_id');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
