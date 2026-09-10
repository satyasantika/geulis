<?php

namespace Database\Seeders;

use App\Models\Rubric;
use App\Models\RubricCriteria;
use Illuminate\Database\Seeder;

/**
 * Rubrik analitik empat tingkat untuk produk akhir — empat kriteria berbobot
 * sama (25 %): ketepatan matematis, kedalaman CT, keterkaitan konteks budaya,
 * kejelasan komunikasi. Ditampilkan ke siswa SEBELUM mengerjakan.
 */
class RubricSeeder extends Seeder
{
    public function run(): void
    {
        $rubrik = Rubric::query()->updateOrCreate(['untuk' => 'produk_akhir'], ['nama' => 'Rubrik Produk Akhir']);

        $kriteria = [
            ['Ketepatan matematis', [
                1 => 'Transformasi yang dipakai keliru atau tidak dinamai.',
                2 => 'Transformasi dinamai benar, parameter (vektor/sudut/faktor) sebagian keliru.',
                3 => 'Transformasi dan parameter benar; satu kekeliruan kecil pada komposisi.',
                4 => 'Semua transformasi, parameter, dan komposisinya tepat dan konsisten.',
            ]],
            ['Kedalaman CT', [
                1 => 'Tidak ada pemecahan motif; langkah ditulis acak.',
                2 => 'Motif dipecah, tetapi pola pengulangan tidak dinyatakan.',
                3 => 'Motif dipecah, pola dinyatakan, algoritma runtut tetapi bertele-tele.',
                4 => 'Dekomposisi, pola, abstraksi parameter, dan algoritma ringkas (memakai perulangan) tampak jelas.',
            ]],
            ['Keterkaitan konteks budaya', [
                1 => 'Artefak hanya disebut, tidak dihubungkan dengan matematika.',
                2 => 'Hubungan artefak–transformasi disebut tetapi tidak tepat.',
                3 => 'Hubungan tepat, atribusi artefak (perajin/lokasi) tidak lengkap.',
                4 => 'Hubungan tepat dan bermakna, atribusi artefak lengkap.',
            ]],
            ['Kejelasan komunikasi', [
                1 => 'Sulit diikuti; istilah tidak konsisten.',
                2 => 'Dapat diikuti dengan usaha; beberapa istilah keliru.',
                3 => 'Jelas dan runtut; visual/notasi membantu.',
                4 => 'Sangat jelas; pembaca awam pun bisa mengikuti algoritmanya.',
            ]],
        ];

        foreach ($kriteria as [$nama, $deskriptor]) {
            RubricCriteria::query()->updateOrCreate(
                ['rubric_id' => $rubrik->id, 'kriteria' => $nama],
                ['bobot' => 25, 'deskriptor' => $deskriptor],
            );
        }
    }
}
