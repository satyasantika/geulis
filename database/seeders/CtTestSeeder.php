<?php

namespace Database\Seeders;

use App\Models\CtItem;
use App\Models\CtTest;
use Illuminate\Database\Seeder;

/**
 * Bank soal awal Tes CT (pretest & posttest paralel): tiap tes 8 butir —
 * dua per indikator (satu PG, satu uraian), stimulus berkonteks artefak
 * Priangan Timur. Tim menyempurnakan lewat panel admin; struktur dan
 * rubrik butir 0–4 dipertahankan supaya penskoran tetap sebanding.
 */
class CtTestSeeder extends Seeder
{
    public function run(): void
    {
        $rubrik = [
            '0' => 'Tidak menjawab atau tidak relevan.',
            '1' => 'Menyebut satu unsur yang benar tanpa penjelasan.',
            '2' => 'Sebagian unsur benar; penjelasan belum runtut.',
            '3' => 'Hampir lengkap dan runtut; ada satu kekeliruan kecil.',
            '4' => 'Lengkap, tepat, dan runtut dengan istilah transformasi yang benar.',
        ];

        $paket = [
            'pretest' => [
                'judul' => 'Tes Awal Berpikir Komputasional — Transformasi Geometri',
                'butir' => [
                    ['D', 'pg', 'Perhatikan sketsa motif batik sawoan yang simetris kiri-kanan dan atas-bawah.', 'Bagian terkecil yang cukup digambar sekali agar seluruh motif bisa dibentuk kembali adalah …', ['seluruh motif', 'setengah motif', 'seperempat motif', 'satu titik'], 'seperempat motif'],
                    ['D', 'uraian', 'Sebuah rozet payung geulis punya 8 jari-jari yang sama.', 'Uraikan rozet itu menjadi bagian-bagian yang lebih kecil, dan sebutkan berapa bagian yang benar-benar perlu digambar. Jelaskan alasanmu.', null, null],
                    ['P', 'pg', 'Pada anyaman bambu Rajapolah, kotak-kotak gelap berulang setiap 2 satuan ke kanan dan 2 satuan ke atas.', 'Jika satu kotak gelap ada di (1, 1), kotak gelap berikutnya pada baris yang sama ada di …', ['(2, 1)', '(3, 1)', '(1, 3)', '(3, 3)'], '(3, 1)'],
                    ['P', 'uraian', 'Deretan motif pada kain: ◐ ◑ ◐ ◑ ◐ ◑ …', 'Jelaskan aturan pengulangannya dengan istilah transformasi, lalu tuliskan motif ke-10.', null, null],
                    ['A', 'pg', 'Sebuah payung geulis memiliki 12 jari-jari yang tersebar merata.', 'Sudut putar terkecil yang memetakan rozet ke dirinya sendiri adalah …', ['12°', '30°', '45°', '60°'], '30°'],
                    ['A', 'uraian', 'Motif daun pada batik dicerminkan sehingga setiap titik (a, b) berpindah ke (−a, b).', 'Nyatakan transformasi itu dengan nama dan garis cerminnya. Tunjukkan dengan satu contoh titik.', null, null],
                    ['Al', 'pg', 'Untuk membuat 4 kelopak dari satu kelopak dengan pusat O, seorang siswa menulis: putar 90°, putar 90°, putar 90°.', 'Urutan perintah yang setara tetapi paling ringkas adalah …', ['ulangi 4 kali: putar 90°', 'ulangi 3 kali: putar 90°', 'putar 270°', 'cerminkan lalu putar 180°'], 'ulangi 3 kali: putar 90°'],
                    ['Al', 'uraian', 'Kamu ingin membuat kisi 3 × 3 kotak dari satu kotak dengan perintah geser dan ulangi.', 'Tuliskan urutan perintah selengkap dan seringkas mungkin, lalu jelaskan mengapa urutanmu benar.', null, null],
                ],
            ],
            'posttest' => [
                'judul' => 'Tes Akhir Berpikir Komputasional — Transformasi Geometri',
                'butir' => [
                    ['D', 'pg', 'Motif merak ngibing pada batik Tasikmalaya memiliki satu sumbu simetri tegak.', 'Bagian terkecil yang cukup digambar sekali agar motif bisa dibentuk kembali adalah …', ['seluruh motif', 'setengah motif', 'seperempat motif', 'satu bulu'], 'setengah motif'],
                    ['D', 'uraian', 'Sebuah rozet payung geulis punya 6 jari-jari yang sama.', 'Uraikan rozet itu menjadi bagian-bagian terkecil yang perlu digambar, dan jelaskan alasanmu.', null, null],
                    ['P', 'pg', 'Kotak gelap pada anyaman berulang setiap 3 satuan ke kanan.', 'Jika satu kotak gelap ada di (2, 0), kotak gelap ketiga pada baris itu ada di …', ['(5, 0)', '(6, 0)', '(8, 0)', '(9, 0)'], '(8, 0)'],
                    ['P', 'uraian', 'Deretan motif pada kain: ▲ ▼ ▲ ▼ ▲ …', 'Jelaskan aturan pengulangannya dengan istilah transformasi, lalu tuliskan motif ke-9.', null, null],
                    ['A', 'pg', 'Sebuah payung geulis memiliki 9 jari-jari yang tersebar merata.', 'Sudut putar terkecil yang memetakan rozet ke dirinya sendiri adalah …', ['9°', '20°', '40°', '45°'], '40°'],
                    ['A', 'uraian', 'Motif pada batik dipetakan sehingga setiap titik (a, b) berpindah ke (b, a).', 'Nyatakan transformasi itu dengan nama dan garis cerminnya. Tunjukkan dengan satu contoh titik.', null, null],
                    ['Al', 'pg', 'Untuk membuat 6 kelopak dari satu kelopak dengan pusat O, seorang siswa menulis lima kali "putar 60°".', 'Urutan yang setara tetapi paling ringkas adalah …', ['ulangi 5 kali: putar 60°', 'ulangi 6 kali: putar 60°', 'putar 300°', 'cerminkan lalu putar 120°'], 'ulangi 5 kali: putar 60°'],
                    ['Al', 'uraian', 'Kamu ingin membuat kisi 4 × 2 kotak dari satu kotak dengan perintah geser dan ulangi.', 'Tuliskan urutan perintah selengkap dan seringkas mungkin, lalu jelaskan mengapa urutanmu benar.', null, null],
                ],
            ],
        ];

        foreach ($paket as $jenis => $data) {
            $tes = CtTest::query()->updateOrCreate(['jenis' => $jenis], ['judul' => $data['judul'], 'durasi_menit' => 60, 'aktif' => false]);

            foreach ($data['butir'] as $i => [$ind, $tipe, $stimulus, $pertanyaan, $pilihan, $kunci]) {
                CtItem::query()->updateOrCreate(['ct_test_id' => $tes->id, 'urutan' => $i + 1], [
                    'indikator' => $ind,
                    'stimulus' => $stimulus,
                    'pertanyaan' => $pertanyaan,
                    'tipe' => $tipe,
                    'pilihan' => $pilihan,
                    'kunci' => $kunci,
                    'rubrik_butir' => $tipe === 'uraian' ? $rubrik : null,
                    'skor_maks' => 4,
                ]);
            }
        }
    }
}
