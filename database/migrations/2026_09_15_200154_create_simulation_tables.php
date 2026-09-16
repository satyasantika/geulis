<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simulasi sementara: kelas + akun guru/siswa yang boleh dibuat dan dihapus
 * kapan saja. Bukan subjek penelitian — kelompok_riset tetap non_riset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('jumlah_kelas');
            $table->unsignedTinyInteger('jumlah_guru');
            $table->unsignedSmallInteger('jumlah_siswa');
            $table->string('layar_token', 64)->unique();
            $table->timestamps();
        });

        Schema::create('simulation_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_run_id')->constrained('simulation_runs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->unsignedSmallInteger('urutan');
            $table->timestamp('diklaim_pada')->nullable();
            $table->timestamps();
            $table->unique(['simulation_run_id', 'urutan']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('simulation_run_id')->nullable()->after('school_id')->constrained('simulation_runs')->nullOnDelete();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('simulation_run_id')->nullable()->after('school_id')->constrained('simulation_runs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('simulation_run_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('simulation_run_id');
        });
        Schema::dropIfExists('simulation_login_tokens');
        Schema::dropIfExists('simulation_runs');
    }
};
