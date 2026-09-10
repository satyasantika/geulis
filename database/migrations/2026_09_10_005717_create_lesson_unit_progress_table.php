<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kemajuan siswa per unit (bagian) pertemuan — syarat "kemajuan tersimpan
 * per bagian" (Sprint 2) dan dasar pengunci pertemuan. Tabel ini tidak ada
 * di kerangka awal karena unit jangkar/eksplorasi/formalisasi tidak
 * meninggalkan artefak lain yang bisa dijadikan penanda selesai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_unit_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_unit_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['sedang', 'selesai'])->default('sedang');
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_unit_progress');
    }
};
