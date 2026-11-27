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
            $table->foreignId('evidence_media_id')
                ->nullable()
                ->after('result')
                ->constrained('media_files')
                ->restrictOnDelete();
        });

        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->foreignId('evidence_media_id')
                ->nullable()
                ->after('result')
                ->constrained('media_files')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->dropForeign(['evidence_media_id']);
            $table->dropColumn('evidence_media_id');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropForeign(['evidence_media_id']);
            $table->dropColumn('evidence_media_id');
        });
    }
};
