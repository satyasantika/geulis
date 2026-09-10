<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('downloads the bundle and unpacks it under public/geogebra without the GeoGebra/ prefix', function (): void {
    $zipPath = tempnam(sys_get_temp_dir(), 'ggb');
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::OVERWRITE);
    $zip->addFromString('GeoGebra/deployggb.js', 'window.GGBApplet = 1;');
    $zip->addFromString('GeoGebra/HTML5/5.0/web3d/web3d.nocache.js', '// web3d');
    $zip->close();

    Http::fake(['download.geogebra.org/*' => Http::response(file_get_contents($zipPath))]);

    $tujuan = public_path('geogebra');
    $cadangan = $tujuan.'-cadangan-uji';
    if (File::isDirectory($tujuan)) {
        File::moveDirectory($tujuan, $cadangan, true);
    }

    try {
        $this->artisan('geulis:pasang-geogebra')->assertSuccessful();

        expect(file_get_contents($tujuan.'/deployggb.js'))->toBe('window.GGBApplet = 1;')
            ->and(File::exists($tujuan.'/HTML5/5.0/web3d/web3d.nocache.js'))->toBeTrue()
            ->and(File::exists(storage_path('app/geogebra-bundle.zip')))->toBeFalse();
    } finally {
        File::deleteDirectory($tujuan);
        if (File::isDirectory($cadangan)) {
            File::moveDirectory($cadangan, $tujuan, true);
        }
        unlink($zipPath);
    }
});

it('fails cleanly when the download is refused', function (): void {
    Http::fake(['download.geogebra.org/*' => Http::response('', 503)]);

    $this->artisan('geulis:pasang-geogebra')->assertFailed();
});
