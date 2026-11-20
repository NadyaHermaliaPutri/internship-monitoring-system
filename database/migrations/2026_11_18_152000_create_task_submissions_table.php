<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('submission_number');
            $table->text('submission_note')->nullable();
            $table->string('submission_url', 2048)->nullable();
            $table->foreignId('evidence_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->dateTime('submitted_at');
            $table->timestamps();

            $table->unique(['task_id', 'submission_number']);
            $table->index(['task_id', 'submitted_at']);
            $table->index('evidence_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
