<?php

namespace App\Console\Commands;

use App\Livewire\Peneliti\Validasi;
use App\Models\ExpertValidation;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SimulasiSeeder;
use Illuminate\Console\Command;

/**
 * php artisan geulis:seed-simulasi — data realistis + akun tetap untuk enam peran.
 */
class SeedSimulasi extends Command
{
    protected $signature = 'geulis:seed-simulasi';

    protected $description = 'Isi data simulasi (siswa, guru, observer, validator, peneliti, admin) dan cetak akun latihan';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => SimulasiSeeder::class, '--force' => true]);

        $base = rtrim((string) config('app.url'), '/');

        $this->newLine();
        $this->info('Masuk staf & siswa: '.$base.'/masuk');
        $this->info('Panel admin: '.$base.'/admin  (surel admin@geulis.test / password)');
        $this->info('Manual: docs/panduan/README.md');
        $this->newLine();

        $this->table(
            ['Peran', 'Username / NIS', 'PIN / sandi', 'Setelah masuk', 'Manual'],
            [
                ...collect(SimulasiSeeder::akunLatihan())->map(fn (array $a): array => [
                    $a['peran'], $a['username'], $a['pin'], $a['layar'], $a['manual'],
                ]),
                ...collect(SimulasiSeeder::SISWA_LATIHAN)->map(fn (array $a, string $nis): array => [
                    'Siswa', $nis, $a['pin'], '/belajar', 'docs/panduan/siswa.md',
                ]),
            ],
        );

        $undangan = ExpertValidation::query()->with('validator')->get();
        if ($undangan->isNotEmpty()) {
            $this->newLine();
            $this->info('Validator memakai tautan bertanda — tanpa akun di /masuk.');
            $this->table(
                ['Validator', 'Status', 'Tautan'],
                $undangan->map(fn (ExpertValidation $v): array => [
                    $v->validator?->nama,
                    $v->status,
                    Validasi::tautan($v),
                ])->all(),
            );
        }

        return self::SUCCESS;
    }
}
