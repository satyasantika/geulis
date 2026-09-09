<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identitas & organisasi: sekolah, kelas, peran.
 * Catatan: siswa masuk memakai NIS + PIN, bukan surel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('npsn', 20)->nullable();
            $table->enum('kabupaten_kota', ['Kota Tasikmalaya', 'Kab. Tasikmalaya', 'Ciamis', 'Banjar', 'Garut', 'Pangandaran', 'Lainnya']);
            $table->string('alamat')->nullable();
            $table->string('kontak_guru')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username', 50)->unique();      // NIS untuk siswa, NIP/inisial untuk guru
            $table->string('email')->nullable()->unique(); // opsional
            $table->string('password');                    // PIN siswa juga di-hash di sini
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('kode_anonim', 12)->nullable()->unique(); // S-001, dipakai pada semua ekspor
            $table->boolean('aktif')->default(true);
            $table->timestamp('terakhir_masuk_pada')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 30)->unique(); // siswa|guru|validator|observer|peneliti|admin
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 50);                 // "XI MIPA 3"
            $table->string('tahun_ajaran', 12);         // "2026/2027"
            $table->enum('kelompok_riset', ['eksperimen', 'kontrol', 'uji_terbatas', 'non_riset'])->default('non_riset');
            $table->string('kode_gabung', 10)->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('setuju_data_penelitian');
            $table->boolean('persetujuan_ortu_diterima')->default(false); // dicatat guru dari formulir kertas
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('schools');
    }
};
