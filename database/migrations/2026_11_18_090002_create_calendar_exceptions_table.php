<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai Database Dictionary v1.0 Seksi 3.6 (Tabel calendar_exceptions).
     */
    public function up(): void
    {
        Schema::create('calendar_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('exception_date')->unique(); // Satu override final per tanggal
            $table->string('type', 20); // holiday atau working_day
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_exceptions');
    }
};
