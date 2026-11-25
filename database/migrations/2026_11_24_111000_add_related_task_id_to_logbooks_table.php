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
        Schema::table('logbooks', function (Blueprint $table) {
            $table->foreignId('related_task_id')
                ->nullable()
                ->after('task_id')
                ->constrained('tasks')
                ->restrictOnDelete();

            $table->index('related_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropForeign(['related_task_id']);
            $table->dropIndex(['related_task_id']);
            $table->dropColumn('related_task_id');
        });
    }
};
