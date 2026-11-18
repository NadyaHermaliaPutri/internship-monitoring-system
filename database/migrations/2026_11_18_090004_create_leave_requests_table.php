<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai Database Dictionary v1.0 Seksi 3.7 (Tabel leave_requests).
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_user_id')->constrained('users')->restrictOnDelete();
            $table->string('leave_type', 20); // leave atau sick
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->foreignId('evidence_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['intern_user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
