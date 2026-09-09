<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konten pembelajaran: 5 pertemuan, unit materi, varian konten (level x modus),
 * aset budaya, dan aktivitas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultural_assets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_motif');
            $table->enum('artefak', ['batik', 'payung_geulis', 'anyaman']);
            $table->string('perajin_sumber');            // WAJIB diisi - atribusi & etika
            $table->string('lokasi');
            $table->date('tanggal_dokumentasi');
            $table->boolean('izin_diperoleh')->default(false);
            $table->string('berkas');                    // path gambar/video
            $table->text('catatan_matematis');           // konsep transformasi yang benar-benar ada pada artefak
            $table->timestamps();
        });

        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('urutan')->unique(); // 1..5
            $table->string('judul');
            $table->string('materi');                        // translasi|refleksi|rotasi|dilatasi|komposisi
            $table->enum('artefak_utama', ['batik', 'payung_geulis', 'anyaman', 'pilihan_siswa']);
            $table->string('fokus_ct');                      // dekomposisi|pola|abstraksi|algoritma
            $table->text('capaian_pembelajaran');
            $table->boolean('terbit')->default(false);
            $table->timestamps();
        });

        Schema::create('lesson_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan');
            $table->string('judul');
            $table->enum('tipe', ['jangkar', 'eksplorasi', 'formalisasi', 'latihan', 'pemeriksaan', 'motif', 'refleksi']);
            $table->boolean('memicu_adaptasi')->default(false); // true hanya untuk tipe 'pemeriksaan'
            $table->timestamps();
            $table->unique(['meeting_id', 'urutan']);
        });

        Schema::create('content_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_unit_id')->constrained()->cascadeOnDelete();
            // '*' berarti berlaku untuk semua nilai pada sumbu itu -- menekan jumlah varian
            $table->string('level', 4)->default('*');   // L1|L2|L3|*
            $table->string('modus', 10)->default('*');  // visual|simbolik|naratif|*
            $table->string('judul');
            $table->longText('badan_konten')->nullable();       // HTML
            $table->foreignId('cultural_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->json('konfigurasi_geogebra')->nullable();   // material_id, perintah awal, alat yang diaktifkan
            $table->enum('status', ['draf', 'revisi', 'siap'])->default('draf');
            $table->timestamps();
            $table->index(['lesson_unit_id', 'level', 'modus']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_unit_id')->constrained()->cascadeOnDelete();
            $table->enum('tipe', ['kuis', 'geogebra', 'motif_builder', 'unggah', 'refleksi']);
            $table->string('judul');
            $table->json('konfigurasi');                 // butir soal, motif sasaran, blok yang tersedia, dsb.
            $table->unsignedSmallInteger('skor_maks')->default(100);
            $table->string('level', 4)->default('*');    // aktivitas boleh dibedakan per level
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('content_variants');
        Schema::dropIfExists('lesson_units');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('cultural_assets');
    }
};
