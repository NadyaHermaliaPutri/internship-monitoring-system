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
            $table->string('activity_status', 30)->nullable()->after('evidence_media_id');
        });

        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->string('activity_status', 30)->nullable()->after('evidence_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbook_revisions', function (Blueprint $table) {
            $table->dropColumn('activity_status');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn('activity_status');
        });
    }
};
