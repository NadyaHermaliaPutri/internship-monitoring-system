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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_user_id')->constrained('users')->restrictOnDelete();
            $table->date('log_date');
            $table->string('title', 150);
            $table->text('activity_description');
            $table->text('result')->nullable();
            $table->string('source', 30)->default('manual'); // manual, task_assignment
            $table->unsignedBigInteger('task_id')->nullable()->unique();
            $table->string('status', 30)->nullable(); // pending, approved, revision_requested (null for task_assignment)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['intern_user_id', 'log_date']);
            $table->index(['source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
