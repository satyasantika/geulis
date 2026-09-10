<?php

namespace Database\Seeders;

use App\Models\ReadinessItem;
use Illuminate\Database\Seeder;

/**
 * 15 butir Tes Kesiapan Prasyarat: koordinat (4), bangun datar (4),
 * simetri (4), bilangan bulat (3). Idempoten; butir dapat direvisi tim
 * lewat seeder ini dan riwayat Git menjadi jejak revisinya.
 */
class ReadinessItemSeeder extends Seeder
{
    public function run(): void
    {
        $butir = [
            ['koordinat', 'Titik A(3, −2) terletak pada kuadran …', ['I', 'II', 'III', 'IV'], 3],
            ['koordinat', 'Titik yang berada 4 satuan ke kanan dari (−1, 2) adalah …', ['(3, 2)', '(−5, 2)', '(−1, 6)', '(−1, −2)'], 0],
            ['koordinat', 'Titik tengah antara (2, 4) dan (6, 8) adalah …', ['(4, 6)', '(8, 12)', '(3, 2)', '(2, 2)'], 0],
            ['koordinat', 'Titik (0, −5) terletak pada …', ['sumbu-x', 'sumbu-y', 'kuadran III', 'titik asal'], 1],
            ['bangun_datar', 'Persegi dengan panjang sisi 6 cm memiliki luas …', ['12 cm²', '24 cm²', '36 cm²', '48 cm²'], 2],
            ['bangun_datar', 'Banyak sisi segi enam beraturan adalah …', ['4', '5', '6', '8'], 2],
            ['bangun_datar', 'Bangun datar dengan dua pasang sisi sejajar dan semua sudutnya siku-siku disebut …', ['jajargenjang', 'persegi panjang', 'trapesium', 'layang-layang'], 1],
            ['bangun_datar', 'Jumlah sudut dalam sebuah segitiga adalah …', ['90°', '180°', '270°', '360°'], 1],
            ['simetri', 'Banyak sumbu simetri sebuah persegi adalah …', ['1', '2', '4', '8'], 2],
            ['simetri', 'Huruf yang memiliki simetri lipat dengan sumbu tegak adalah …', ['S', 'N', 'A', 'Z'], 2],
            ['simetri', 'Bangun yang memiliki simetri putar tingkat 3 adalah …', ['persegi', 'segitiga sama sisi', 'persegi panjang', 'trapesium'], 1],
            ['simetri', 'Jika titik (2, 3) dilipat pada sumbu-x, bayangannya adalah …', ['(−2, 3)', '(2, −3)', '(3, 2)', '(−2, −3)'], 1],
            ['bilangan_bulat', 'Hasil dari (−3) × 4 + 5 adalah …', ['−7', '7', '−17', '17'], 0],
            ['bilangan_bulat', 'Hasil dari −8 − (−5) adalah …', ['−13', '−3', '3', '13'], 1],
            ['bilangan_bulat', 'Nilai dari 2 × (−1)² − 3 adalah …', ['−5', '−1', '1', '5'], 1],
        ];

        foreach ($butir as $i => [$prasyarat, $pertanyaan, $pilihan, $kunci]) {
            ReadinessItem::query()->updateOrCreate(
                ['urutan' => $i + 1],
                ['prasyarat' => $prasyarat, 'pertanyaan' => $pertanyaan, 'pilihan' => $pilihan, 'kunci' => $kunci],
            );
        }
    }
}
