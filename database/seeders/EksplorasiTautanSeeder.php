<?php

namespace Database\Seeders;

use App\Models\ContentVariant;
use App\Models\Meeting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * PENGECUALIAN SEMENTARA sebelum validasi ahli dimulai.
 *
 * MeetingSeeder menegaskan bahwa content_variants seharusnya diisi lewat
 * panel admin A-01, bukan seeder, agar riwayat penyuntingan tercatat untuk
 * validasi ahli. Seeder ini hanya menambahkan tautan referensi luar
 * (simulasi itch.io & applet GeoGebra publik) pada unit "Eksplorasi
 * GeoGebra", bukan konten inti Bulan 5-8 — dijalankan manual sekali:
 *
 *   docker exec geulis-php php artisan db:seed --class=EksplorasiTautanSeeder
 *
 * Tidak didaftarkan di DatabaseSeeder agar tidak ikut migrate:fresh --seed
 * dan tidak menimpa konten yang nanti dibuat lewat panel admin.
 */
class EksplorasiTautanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tautan = [
            'translasi' => [
                'itch' => 'https://rezamohammadrizqi.itch.io/translasi',
                'geogebra' => 'https://www.geogebra.org/m/f5pmpmew',
            ],
            'refleksi' => [
                'itch' => 'https://rezamohammadrizqi.itch.io/refleksi',
                'geogebra' => 'https://www.geogebra.org/m/wskaeb3r',
            ],
            'rotasi' => [
                'itch' => 'https://rezamohammadrizqi.itch.io/rotasi',
                'geogebra' => 'https://www.geogebra.org/m/kuupkavt',
            ],
            'dilatasi' => [
                'itch' => 'https://rezamohammadrizqi.itch.io/dilatasi',
                'geogebra' => 'https://www.geogebra.org/m/hjmeyzke',
            ],
        ];

        foreach ($tautan as $materi => $url) {
            $meeting = Meeting::query()->where('materi', $materi)->first();

            if (! $meeting) {
                $this->command?->warn("Lewati '{$materi}': tidak ada pertemuan dengan materi ini. Jalankan MeetingSeeder dulu.");

                continue;
            }

            $unitEksplorasi = $meeting->lessonUnits()->where('tipe', 'eksplorasi')->first();

            if (! $unitEksplorasi) {
                $this->command?->warn("Lewati '{$materi}': pertemuan #{$meeting->urutan} belum punya unit 'eksplorasi'.");

                continue;
            }

            ContentVariant::query()->updateOrCreate(
                ['lesson_unit_id' => $unitEksplorasi->id, 'level' => '*', 'modus' => '*'],
                [
                    'judul' => 'Simulasi & GeoGebra — '.ucfirst($materi),
                    'badan_konten' => $this->badanKonten($url['itch'], $url['geogebra']),
                    'status' => 'draf',
                ]
            );
        }
    }

    /** Tautan luar yang dibuka di tab baru, bukan di-embed (aturan self-host GeoGebra tetap berlaku untuk embed). */
    private function badanKonten(string $urlItch, string $urlGeogebra): string
    {
        return <<<HTML
        <p>Coba dulu simulasi interaktif berikut sebelum lanjut ke penjelasan.</p>
        <ul>
            <li><a href="{$urlItch}" target="_blank" rel="noopener noreferrer">Buka simulasi interaktif (itch.io)</a></li>
            <li><a href="{$urlGeogebra}" target="_blank" rel="noopener noreferrer">Buka applet GeoGebra</a></li>
        </ul>
        HTML;
    }
}
