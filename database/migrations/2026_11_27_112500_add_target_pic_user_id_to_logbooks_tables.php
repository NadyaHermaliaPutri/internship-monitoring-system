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
            $table->foreignId('target_pic_user_id')
                ->nullable()
                ->after('related_task_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->index('target_pic_user_id');
        });

        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->foreignId('target_pic_user_id')
                ->nullable()
                ->after('evidence_media_id')
                ->constrained('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->dropForeign(['target_pic_user_id']);
            $table->dropColumn('target_pic_user_id');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropForeign(['target_pic_user_id']);
            $table->dropIndex(['target_pic_user_id']);
            $table->dropColumn('target_pic_user_id');
        });
    }
};
