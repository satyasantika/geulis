<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul penelitian: validasi ahli (Aiken's V), angket kepraktisan,
 * lembar observasi, dan riwayat ekspor data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                        // "Validasi Ahli Materi"
            $table->string('sasaran_validator', 60);       // ahli_materi|ahli_media|praktisi
            $table->unsignedTinyInteger('skala_maks')->default(5); // c
            $table->timestamps();
        });

        Schema::create('validation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_instrument_id')->constrained()->cascadeOnDelete();
            $table->string('aspek');                       // kelayakan materi | desain diferensiasi | dst.
            $table->unsignedTinyInteger('urutan');
            $table->text('pernyataan');
            $table->timestamps();
        });

        Schema::create('expert_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validation_instrument_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();         // tautan bertanda, tanpa perlu daftar akun
            $table->enum('status', ['dikirim', 'dibuka', 'selesai'])->default('dikirim');
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('validation_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_validation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validation_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');           // r, 1..skala_maks
            $table->text('saran')->nullable();
            $table->timestamps();
            $table->unique(['expert_validation_id', 'validation_item_id'], 'validation_ratings_validasi_butir_unique'); // nama otomatis 65 karakter, MySQL membatasi 64
        });

        Schema::create('aiken_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('nilai_v', 4, 3);
            $table->string('kategori', 20);                // rendah|sedang|tinggi
            $table->unsignedTinyInteger('n_penilai');
            $table->timestamp('dihitung_pada');
            $table->timestamps();
        });

        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                        // "Angket Respons Siswa"
            $table->enum('sasaran', ['siswa', 'guru']);
            $table->unsignedTinyInteger('skala_maks')->default(4);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('questionnaire_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->string('aspek');
            $table->unsignedTinyInteger('urutan');
            $table->text('pernyataan');
            $table->boolean('butir_negatif')->default(false); // skor dibalik saat dihitung
            $table->timestamps();
        });

        Schema::create('questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->timestamps();
            $table->unique(['user_id', 'questionnaire_item_id']);
        });

        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('catatan_lapangan')->nullable();
            $table->timestamps();
        });

        Schema::create('observation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('urutan');
            $table->text('pernyataan');
            $table->string('aspek', 60);
            $table->timestamps();
        });

        Schema::create('observation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('observation_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');           // 1..4
            $table->timestamps();
        });

        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('jenis', 40);
            $table->json('parameter')->nullable();
            $table->string('berkas');
            $table->boolean('dianonimkan')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'data_exports', 'observation_records', 'observation_items', 'observations',
            'questionnaire_responses', 'questionnaire_items', 'questionnaires',
            'aiken_results', 'validation_ratings', 'expert_validations',
            'validation_items', 'validation_instruments',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
