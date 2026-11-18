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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pic_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('intern_user_id')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('description');
            $table->dateTime('assigned_at');
            $table->dateTime('deadline_at')->nullable();
            $table->string('status', 30)->default('assigned');
            $table->dateTime('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('pic_user_id');
            $table->index('intern_user_id');
            $table->index('status');
            $table->index('deadline_at');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
        });

        Schema::dropIfExists('tasks');
    }
};
