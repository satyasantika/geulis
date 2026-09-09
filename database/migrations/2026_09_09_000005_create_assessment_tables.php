<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tes Computational Thinking (pra/pasca), produk siswa, dan rubrik analitik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ct_tests', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['pretest', 'posttest']);
            $table->string('judul');
            $table->unsignedSmallInteger('durasi_menit')->default(60);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('ct_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ct_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan');
            // D = dekomposisi, P = pengenalan pola, A = abstraksi, Al = algoritma
            $table->enum('indikator', ['D', 'P', 'A', 'Al']);
            $table->text('stimulus')->nullable();               // konteks budaya (gambar/cerita artefak)
            $table->foreignId('cultural_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->text('pertanyaan');
            $table->enum('tipe', ['pg', 'isian', 'uraian']);
            $table->json('pilihan')->nullable();
            $table->text('kunci')->nullable();
            $table->json('rubrik_butir')->nullable();           // untuk uraian: deskriptor 0-4
            $table->unsignedTinyInteger('skor_maks')->default(4);
            $table->timestamps();
        });

        Schema::create('ct_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ct_item_id')->constrained()->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->decimal('skor_otomatis', 4, 2)->nullable();
            $table->decimal('skor_manual', 4, 2)->nullable();
            $table->foreignId('penilai_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_penilai')->nullable();
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'ct_item_id']);
        });

        Schema::create('ct_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ct_test_id')->constrained()->cascadeOnDelete();
            $table->decimal('skor_d', 5, 2)->default(0);
            $table->decimal('skor_p', 5, 2)->default(0);
            $table->decimal('skor_a', 5, 2)->default(0);
            $table->decimal('skor_al', 5, 2)->default(0);
            $table->decimal('skor_total', 6, 2)->default(0);
            $table->decimal('persen', 5, 2)->default(0);        // 0..100, dasar perhitungan N-Gain
            $table->timestamp('dihitung_pada')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'ct_test_id']);
        });

        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('untuk', 40);                        // produk_akhir|uraian_ct
            $table->timestamps();
        });

        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $table->string('kriteria');                         // ketepatan matematis, kedalaman CT, dst.
            $table->unsignedTinyInteger('bobot')->default(25);  // persen
            $table->json('deskriptor');                         // 4 tingkat: {1:"...",2:"...",3:"...",4:"..."}
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->enum('bentuk', ['desain_motif', 'laporan_algoritmik', 'video', 'poster']);
            $table->string('berkas')->nullable();
            $table->string('tautan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_criteria_id')->constrained('rubric_criteria')->cascadeOnDelete(); // nama tabel tak berjamak — Laravel menebak 'rubric_criterias'
            $table->foreignId('penilai_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('tingkat');             // 1..4
            $table->text('umpan_balik')->nullable();
            $table->timestamps();
        });

        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('pertanyaan');
            $table->text('jawaban');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reflections');
        Schema::dropIfExists('rubric_scores');
        Schema::dropIfExists('products');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
        Schema::dropIfExists('ct_scores');
        Schema::dropIfExists('ct_responses');
        Schema::dropIfExists('ct_items');
        Schema::dropIfExists('ct_tests');
    }
};
