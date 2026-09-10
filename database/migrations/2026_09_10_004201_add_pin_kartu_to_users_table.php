<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PIN siswa disimpan DUA kali: di-hash pada `password` untuk verifikasi masuk,
 * dan terenkripsi (APP_KEY) pada `pin_kartu` supaya guru bisa mencetak ulang
 * kartu PIN (G-05) kapan saja tanpa harus mengatur ulang PIN seluruh kelas.
 * Salinan basis data tanpa APP_KEY tidak membuka PIN siapa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('pin_kartu')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin_kartu');
        });
    }
};
