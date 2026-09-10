<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\LessonUnit;
use App\Models\Meeting;
use Illuminate\Database\Seeder;

/**
 * Motif sasaran Motif Builder untuk kelima pertemuan — aproksimasi geometris
 * dari artefak (kisi anyaman, simetri lipat batik, rozet payung, motif
 * berlapis, komposisi). Tim konten menyesuaikan bentuk motif dasar dari
 * dokumentasi lapangan lewat A-01; struktur kuncinya tetap.
 *
 * Kisi kanvas −6..6. Indeks pada kunci.motif_dasar merujuk urutan poligon
 * hasil `sasaran` (poligon ke-0 selalu motif dasar asli).
 */
class MotifSasaranSeeder extends Seeder
{
    public function run(): void
    {
        $kotak = [[-5, -5], [-3, -5], [-3, -3], [-5, -3]];
        $daun = [[0.5, 0.5], [3, 1], [4, 3], [2.5, 4.5], [1, 3]];
        $kelopak = [[0, 0.5], [1, 2], [0.6, 4], [0, 5], [-0.6, 4], [-1, 2]];
        $kotakKecil = [[1, 1], [2, 1], [2, 2], [1, 2]];
        $segitiga = [[0, 0], [1.5, 0], [0.75, 1.5]];

        $sasaran = [
            1 => [
                'judul' => 'Susun kisi anyaman hanya dengan translasi',
                'motif_dasar' => $kotak,
                'sasaran' => [
                    ['op' => 'MOTIF_DASAR'],
                    ['op' => 'ULANGI', 'n' => 2, 'badan' => [['op' => 'TRANSLASI', 'vektor' => [4, 0]]]],
                    ['op' => 'ULANGI', 'n' => 2, 'badan' => [['op' => 'TRANSLASI', 'vektor' => [0, 4]]]],
                ],
                'blok' => ['TRANSLASI', 'ULANGI'],
                'langkah_minimum' => 4,
                'kunci' => ['motif_dasar' => range(0, 8), 'jumlah_motif_dasar' => 1, 'n_pengulangan' => 2,
                    'parameter' => ['TRANSLASI' => ['vektor' => ['salah_satu' => [[4, 0], [0, 4]]]]]],
            ],
            2 => [
                'judul' => 'Rekonstruksi motif sawoan bersimetri lipat',
                'motif_dasar' => $daun,
                'sasaran' => [
                    ['op' => 'MOTIF_DASAR'],
                    ['op' => 'REFLEKSI', 'garis' => 'y'],
                    ['op' => 'REFLEKSI', 'garis' => 'x'],
                    ['op' => 'REFLEKSI', 'garis' => 'y'],
                ],
                'blok' => ['REFLEKSI', 'ROTASI', 'ULANGI'],
                'langkah_minimum' => 3,
                'kunci' => ['motif_dasar' => [0, 1, 2, 3], 'jumlah_motif_dasar' => 1, 'n_pengulangan' => 0,
                    'parameter' => ['REFLEKSI' => ['garis' => ['salah_satu' => ['x', 'y']]]]],
            ],
            3 => [
                'judul' => 'Bangun rozet payung geulis 8 jari-jari',
                'motif_dasar' => $kelopak,
                'sasaran' => [
                    ['op' => 'MOTIF_DASAR'],
                    ['op' => 'ULANGI', 'n' => 8, 'badan' => [['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45]]],
                ],
                'blok' => ['ROTASI', 'REFLEKSI', 'ULANGI'],
                'langkah_minimum' => 2,
                'kunci' => ['motif_dasar' => range(0, 8), 'jumlah_motif_dasar' => 1, 'n_pengulangan' => 8,
                    'parameter' => ['ROTASI' => ['sudut' => 45, 'pusat' => [0, 0]]]],
            ],
            4 => [
                'judul' => 'Motif berlapis: perbesar dan balikkan',
                'motif_dasar' => $kotakKecil,
                'sasaran' => [
                    ['op' => 'MOTIF_DASAR'],
                    ['op' => 'ULANGI', 'n' => 2, 'badan' => [['op' => 'DILATASI', 'pusat' => [0, 0], 'k' => 2]]],
                    ['op' => 'DILATASI', 'pusat' => [0, 0], 'k' => -1],
                ],
                'blok' => ['DILATASI', 'TRANSLASI', 'ULANGI'],
                'langkah_minimum' => 3,
                'kunci' => ['motif_dasar' => [0], 'jumlah_motif_dasar' => 1, 'n_pengulangan' => 2,
                    'parameter' => ['DILATASI' => ['k' => ['salah_satu' => [2, -1]], 'pusat' => [0, 0]]]],
            ],
            5 => [
                'judul' => 'Komposisi: geser lalu putar',
                'motif_dasar' => $segitiga,
                'sasaran' => [
                    ['op' => 'MOTIF_DASAR'],
                    ['op' => 'TRANSLASI', 'vektor' => [3, 0]],
                    ['op' => 'ULANGI', 'n' => 4, 'badan' => [['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 90]]],
                ],
                'blok' => ['TRANSLASI', 'REFLEKSI', 'ROTASI', 'DILATASI', 'ULANGI'],
                'langkah_minimum' => 3,
                'kunci' => ['motif_dasar' => [0], 'jumlah_motif_dasar' => 1, 'n_pengulangan' => 4,
                    'parameter' => ['TRANSLASI' => ['vektor' => [3, 0]], 'ROTASI' => ['sudut' => 90, 'pusat' => [0, 0]]]],
            ],
        ];

        foreach ($sasaran as $urutan => $k) {
            $meeting = Meeting::query()->where('urutan', $urutan)->first();
            if ($meeting === null) {
                continue;
            }
            $unit = LessonUnit::query()->where('meeting_id', $meeting->id)->where('tipe', 'motif')->first();
            if ($unit === null) {
                continue;
            }

            Activity::query()->updateOrCreate(
                ['lesson_unit_id' => $unit->id, 'tipe' => 'motif_builder'],
                [
                    'judul' => $k['judul'],
                    'konfigurasi' => ['kisi' => ['min' => -6, 'maks' => 6]] + $k,
                    'skor_maks' => 100,
                    'level' => '*',
                ],
            );
        }
    }
}
