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
        Schema::create('logbook_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->restrictOnDelete();
            $table->unsignedInteger('revision_no');
            $table->date('log_date');
            $table->string('title', 150);
            $table->text('activity_description');
            $table->text('result')->nullable();
            $table->string('status_before', 30)->nullable();
            $table->text('review_note_before')->nullable();
            $table->foreignId('revised_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['logbook_id', 'revision_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_revisions');
    }
};
