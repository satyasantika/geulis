<?php

namespace Database\Seeders;

use App\Models\LessonUnit;
use App\Models\Meeting;
use Illuminate\Database\Seeder;

/**
 * Kerangka 5 pertemuan beserta 7 unit tetap per pertemuan.
 *
 * Seeder ini hanya membuat KERANGKA. Isi konten (content_variants) diproduksi
 * tim pada Bulan 5-8 dan dimasukkan lewat panel admin A-01, bukan lewat seeder,
 * agar riwayat penyuntingannya terekam dan bisa divalidasi ahli.
 */
class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $pertemuan = [
            [
                'urutan' => 1,
                'judul' => 'Translasi pada Pola Anyaman Rajapolah',
                'materi' => 'translasi',
                'artefak_utama' => 'anyaman',
                'fokus_ct' => 'pengenalan_pola',
                'capaian_pembelajaran' => 'Siswa dapat menentukan bayangan titik dan bangun oleh translasi, serta mengenali satuan pengulangan pada pola kisi anyaman bambu Rajapolah.',
            ],
            [
                'urutan' => 2,
                'judul' => 'Refleksi pada Motif Batik Sawoan Tasikmalaya',
                'materi' => 'refleksi',
                'artefak_utama' => 'batik',
                'fokus_ct' => 'dekomposisi',
                'capaian_pembelajaran' => 'Siswa dapat menentukan bayangan oleh refleksi terhadap sumbu koordinat dan garis y = x / y = -x, serta memecah motif batik menjadi motif dasar dan sumbu-sumbu lipatnya.',
            ],
            [
                'urutan' => 3,
                'judul' => 'Rotasi pada Rozet Payung Geulis',
                'materi' => 'rotasi',
                'artefak_utama' => 'payung_geulis',
                'fokus_ct' => 'abstraksi',
                'capaian_pembelajaran' => 'Siswa dapat menentukan bayangan oleh rotasi terhadap pusat tertentu, dan merumuskan sudut putar dari banyaknya jari-jari payung geulis (360 derajat / n).',
            ],
            [
                'urutan' => 4,
                'judul' => 'Dilatasi pada Motif Berlapis',
                'materi' => 'dilatasi',
                'artefak_utama' => 'payung_geulis',
                'fokus_ct' => 'abstraksi',
                'capaian_pembelajaran' => 'Siswa dapat menentukan bayangan oleh dilatasi dengan faktor skala k, serta membandingkan pengaruh k > 1, 0 < k < 1, dan k < 0 pada motif konsentris.',
            ],
            [
                'urutan' => 5,
                'judul' => 'Komposisi Transformasi dan Proyek Motif Orisinal',
                'materi' => 'komposisi',
                'artefak_utama' => 'pilihan_siswa',
                'fokus_ct' => 'algoritma',
                'capaian_pembelajaran' => 'Siswa dapat menyusun komposisi transformasi, menjelaskan sifat non-komutatifnya, dan merancang motif orisinal beserta algoritma pembentuknya.',
            ],
        ];

        // Tujuh unit tetap - struktur identik di kelima pertemuan supaya siswa
        // tidak perlu belajar ulang cara memakai sistem, dan data antar-pertemuan sebanding.
        $unitBaku = [
            ['tipe' => 'jangkar',     'judul' => 'Jangkar Budaya',        'memicu_adaptasi' => false],
            ['tipe' => 'eksplorasi',  'judul' => 'Eksplorasi GeoGebra',   'memicu_adaptasi' => false],
            ['tipe' => 'formalisasi', 'judul' => 'Formalisasi Konsep',    'memicu_adaptasi' => false],
            ['tipe' => 'latihan',     'judul' => 'Latihan Berjenjang',    'memicu_adaptasi' => false],
            ['tipe' => 'pemeriksaan', 'judul' => 'Pemeriksaan Penguasaan', 'memicu_adaptasi' => true],
            ['tipe' => 'motif',       'judul' => 'Motif Builder',         'memicu_adaptasi' => false],
            ['tipe' => 'refleksi',    'judul' => 'Refleksi Belajar',      'memicu_adaptasi' => false],
        ];

        foreach ($pertemuan as $data) {
            $meeting = Meeting::updateOrCreate(['urutan' => $data['urutan']], $data + ['terbit' => false]);

            foreach ($unitBaku as $i => $unit) {
                LessonUnit::updateOrCreate(
                    ['meeting_id' => $meeting->id, 'urutan' => $i + 1],
                    $unit
                );
            }
        }
    }
}
