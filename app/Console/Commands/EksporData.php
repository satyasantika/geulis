<?php

namespace App\Console\Commands;

use App\Models\DataExport;
use App\Models\User;
use App\Services\Research\PengeksporData;
use Illuminate\Console\Command;

/**
 * php artisan geulis:ekspor — .xlsx multi-lembar, selalu kode anonim.
 */
class EksporData extends Command
{
    protected $signature = 'geulis:ekspor {--oleh= : ID pengguna peneliti yang mencatat ekspor}';

    protected $description = 'Ekspor data penelitian ke .xlsx (siswa, tes CT, N-Gain, mastery, adaptasi, motif, angket, validasi, observasi, refleksi) dengan kode anonim';

    public function handle(PengeksporData $pengekspor): int
    {
        if (! config('geulis.anonimkan_ekspor', true)) {
            $this->error('anonimkan_ekspor = false. Ekspor dibatalkan.');

            return self::FAILURE;
        }

        $nama = 'geulis-'.now()->format('Ymd-His').'.xlsx';
        $path = storage_path('app/ekspor/'.$nama);

        $lembar = $pengekspor->lembar();
        $siswa = User::query()->whereHas('roles', fn ($q) => $q->where('nama', 'siswa'))->get(['id', 'nama', 'username']);
        if ($pengekspor->mengandungIdentitas($lembar, $siswa)) {
            $this->error('Ekspor dibatalkan: ditemukan nama/NIS siswa di lembar.');

            return self::FAILURE;
        }

        $pengekspor->tulis($path);

        $oleh = $this->option('oleh') ? User::query()->find($this->option('oleh')) : User::query()->whereHas('roles', fn ($q) => $q->where('nama', 'peneliti'))->first();
        if ($oleh !== null) {
            DataExport::query()->create([
                'dibuat_oleh' => $oleh->getKey(),
                'jenis' => 'xlsx_lengkap',
                'parameter' => ['lembar' => array_keys($lembar), 'baris' => array_map(fn ($l) => count($l) - 1, $lembar)],
                'berkas' => 'ekspor/'.$nama,
                'dianonimkan' => true,
            ]);
        }

        $this->info('Ekspor selesai: '.$path.' ('.count($lembar).' lembar)');

        return self::SUCCESS;
    }
}
