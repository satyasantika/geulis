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
        Schema::table('questionnaires', function (Blueprint $table) {
            // Default 'kepraktisan' membackfill 2 instrumen yang sudah ada,
            // supaya query lama yang belum mengecek jenis tetap aman.
            $table->enum('jenis', ['kepraktisan', 'persepsi'])->default('kepraktisan')->after('sasaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
