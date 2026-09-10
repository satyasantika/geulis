<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tes CT punya tiga waktu: mulai (created_at), dikumpulkan siswa
 * (dikumpulkan_pada — kolom baru), dan selesai dihitung setelah uraian
 * dinilai guru (dihitung_pada). Tanpa kolom ini, tes yang dikumpulkan tetapi
 * uraiannya belum dinilai tidak bisa dibedakan dari tes yang masih berjalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ct_scores', function (Blueprint $table) {
            $table->timestamp('dikumpulkan_pada')->nullable()->after('persen');
        });
    }

    public function down(): void
    {
        Schema::table('ct_scores', function (Blueprint $table) {
            $table->dropColumn('dikumpulkan_pada');
        });
    }
};
