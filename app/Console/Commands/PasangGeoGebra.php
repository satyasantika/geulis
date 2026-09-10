<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use ZipArchive;

/**
 * Mengunduh GeoGebra Math Apps Bundle dan memasangnya di public/geogebra/
 * supaya applet dimuat dari server sendiri, bukan dari CDN yang diblokir
 * jaringan sekolah. Bundel tidak masuk Git (lisensi non-komersial GeoGebra,
 * bukan bagian ciptaan yang didaftarkan) — perintah ini yang membuatnya
 * dapat direproduksi di server mana pun.
 */
class PasangGeoGebra extends Command
{
    protected $signature = 'geulis:pasang-geogebra {--sumber=https://download.geogebra.org/package/geogebra-math-apps-bundle : URL bundel zip}';

    protected $description = 'Unduh dan pasang bundel GeoGebra Math Apps ke public/geogebra (swadaya, tanpa CDN)';

    public function handle(): int
    {
        $tujuan = public_path('geogebra');
        $zip = storage_path('app/geogebra-bundle.zip');

        $this->info('Mengunduh bundel GeoGebra…');
        $respons = Http::timeout(600)->get($this->option('sumber'));

        if (! $respons->successful()) {
            $this->error('Gagal mengunduh: HTTP '.$respons->status());

            return self::FAILURE;
        }

        file_put_contents($zip, $respons->body());

        $arsip = new ZipArchive;
        if ($arsip->open($zip) !== true) {
            $this->error('Berkas zip tidak bisa dibuka.');

            return self::FAILURE;
        }

        $this->info('Mengekstrak ke public/geogebra…');
        for ($i = 0; $i < $arsip->numFiles; $i++) {
            $nama = $arsip->getNameIndex($i);
            if (! str_starts_with($nama, 'GeoGebra/') || str_ends_with($nama, '/')) {
                continue;
            }
            $relatif = substr($nama, strlen('GeoGebra/'));
            $path = $tujuan.'/'.$relatif;
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $arsip->getFromIndex($i));
        }
        $arsip->close();
        unlink($zip);

        $this->info('Selesai. deployggb.js: '.$tujuan.'/deployggb.js');

        return self::SUCCESS;
    }
}
