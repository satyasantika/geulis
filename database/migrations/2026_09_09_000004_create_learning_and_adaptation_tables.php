<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kegiatan belajar, status penguasaan, dan JEJAK KEPUTUSAN MESIN DIFERENSIASI.
 *
 * Tabel adaptation_logs adalah bukti empiris bahwa sistem benar-benar adaptif.
 * Tanpa tabel ini, klaim "adaptive learning" pada artikel tidak dapat dibuktikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->enum('level_kini', ['L1', 'L2', 'L3'])->default('L2');
            $table->enum('modus_kini', ['visual', 'simbolik', 'naratif', 'campuran'])->default('campuran');
            $table->enum('status', ['aktif', 'selesai', 'keluar'])->default('aktif');
            $table->timestamps();
            $table->unique(['user_id', 'classroom_id']);
        });

        Schema::create('activity_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('percobaan_ke')->default(1);
            $table->json('jawaban')->nullable();
            $table->decimal('skor', 5, 2)->nullable();          // 0..100
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->timestamp('mulai_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->boolean('dugaan_menebak')->default(false);  // dipicu RULE_GUESS_GUARD
            $table->timestamps();
            $table->index(['user_id', 'activity_id']);
        });

        Schema::create('mastery_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('nilai_m', 4, 3)->default(0.500);   // M dalam [0,1]
            $table->enum('level_saat_itu', ['L1', 'L2', 'L3']);
            $table->unsignedTinyInteger('iterasi_remedial')->default(0);
            $table->boolean('perlu_pendampingan')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'lesson_unit_id']);
        });

        Schema::create('adaptation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_unit_id')->constrained()->cascadeOnDelete();
            $table->string('kode_aturan', 32);                  // RULE_PROMOTE, RULE_REMEDIATE, dst.
            $table->decimal('m_sebelum', 4, 3);
            $table->decimal('m_sesudah', 4, 3);
            $table->decimal('skor_pemeriksaan', 4, 3);          // s
            $table->enum('level_sebelum', ['L1', 'L2', 'L3']);
            $table->enum('level_sesudah', ['L1', 'L2', 'L3']);
            $table->string('keputusan');                        // ringkasan yang dapat dibaca manusia
            $table->json('konteks')->nullable();                // alpha, ambang, butir yang salah
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('kode_aturan');
        });

        Schema::create('teacher_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('level_lama', ['L1', 'L2', 'L3']);
            $table->enum('level_baru', ['L1', 'L2', 'L3']);
            $table->text('alasan');                             // WAJIB - data kualitatif berharga
            $table->timestamps();
        });

        Schema::create('motif_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('percobaan_ke')->default(1);
            $table->json('urutan_perintah');                    // [{op:"ROTASI",pusat:[0,0],sudut:45}, ...]
            $table->decimal('skor_kemiripan', 5, 2);            // IoU x 100
            $table->unsignedSmallInteger('langkah_siswa');
            $table->unsignedSmallInteger('langkah_minimum');
            $table->decimal('efisiensi', 5, 2);
            $table->json('motif_dasar_ditandai')->nullable();   // bukti dekomposisi
            $table->boolean('lolos')->default(false);           // kemiripan >= 90
            $table->longText('cuplikan_svg')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motif_submissions');
        Schema::dropIfExists('teacher_overrides');
        Schema::dropIfExists('adaptation_logs');
        Schema::dropIfExists('mastery_states');
        Schema::dropIfExists('activity_attempts');
        Schema::dropIfExists('enrollments');
    }
};
