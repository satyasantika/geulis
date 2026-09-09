<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asesmen awal: tes kesiapan prasyarat, profil belajar, minat konteks budaya,
 * dan hasil penempatan oleh Mesin Diferensiasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('readiness_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('urutan');
            $table->text('pertanyaan');
            $table->json('pilihan');            // ["(7, -1)", "(-1, -1)", ...]
            $table->unsignedTinyInteger('kunci'); // indeks pilihan benar
            $table->string('prasyarat', 60);    // koordinat|bangun_datar|simetri|bilangan_bulat
            $table->timestamps();
        });

        Schema::create('readiness_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('readiness_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('jawaban')->nullable();
            $table->boolean('benar')->default(false);
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'readiness_item_id']);
        });

        Schema::create('learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_visual');    // 0-100
            $table->unsignedTinyInteger('skor_simbolik');
            $table->unsignedTinyInteger('skor_naratif');
            $table->enum('modus_utama', ['visual', 'simbolik', 'naratif', 'campuran']);
            $table->enum('artefak_pilihan', ['batik', 'payung_geulis', 'anyaman'])->nullable();
            $table->json('jawaban_mentah')->nullable();    // disimpan untuk analisis ulang
            $table->timestamps();
        });

        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_readiness'); // R, 0-100
            $table->enum('level_awal', ['L1', 'L2', 'L3']);
            $table->enum('modus', ['visual', 'simbolik', 'naratif', 'campuran']);
            $table->enum('artefak_utama', ['batik', 'payung_geulis', 'anyaman']);
            $table->json('penjelasan');                    // aturan yang dipakai, untuk audit & artikel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placements');
        Schema::dropIfExists('learning_profiles');
        Schema::dropIfExists('readiness_responses');
        Schema::dropIfExists('readiness_items');
    }
};
