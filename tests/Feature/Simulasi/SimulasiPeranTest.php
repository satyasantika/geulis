<?php

use App\Enums\Peran;
use App\Livewire\Peneliti\Validasi;
use App\Models\Classroom;
use App\Models\ExpertValidation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SimulasiSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('simulasi enam peran', function (): void {
    beforeEach(function (): void {
        $this->seed(DatabaseSeeder::class);
        $this->seed(SimulasiSeeder::class);
    });

    it('lets every staff training account sign in to its home screen', function (): void {
        foreach (SimulasiSeeder::akunLatihan() as $akun) {
            $user = User::query()->where('username', $akun['username'])->first();

            expect($user)->not->toBeNull();

            post('/masuk', ['username' => $akun['username'], 'pin' => $akun['pin']])
                ->assertRedirect($user->rutePulang());

            expect(parse_url($user->rutePulang(), PHP_URL_PATH))->toBe($akun['layar']);

            auth()->logout();
        }
    });

    it('keeps walkthrough student PINs and opens the student area', function (string $nis, string $pin, bool $belumMulai): void {
        $siswa = User::query()->where('username', $nis)->first();

        expect($siswa)->not->toBeNull()
            ->and($siswa->punyaPeran(Peran::Siswa))->toBeTrue();

        post('/masuk', ['username' => $nis, 'pin' => $pin])->assertRedirect();

        $halaman = get('/belajar');

        if ($belumMulai) {
            $halaman->assertRedirect(route('siswa.persetujuan'));
        } else {
            $halaman->assertOk()->assertSee('Halo');
        }
    })->with([
        'tuntas' => ['20260301', '482913', false],
        'pendampingan' => ['20260313', '739182', false],
        'belum mulai' => ['20260320', '615204', true],
    ]);

    it('gives unfinished validators a signed link instead of a login', function (): void {
        $menunggu = ExpertValidation::query()->where('status', 'dikirim')->first();

        expect($menunggu)->not->toBeNull();

        get(Validasi::tautan($menunggu))
            ->assertOk()
            ->assertSee('validasi ahli');
    });

    it('has four research classrooms and opens guru, peneliti, and observer homes', function (): void {
        expect(Classroom::query()->whereIn('kelompok_riset', ['eksperimen', 'kontrol'])->count())->toBe(4);

        actingAs(User::query()->where('username', 'guru-sim-1')->firstOrFail())
            ->get('/guru')->assertOk()->assertSee('XI-3')->assertSee('XI-4');

        actingAs(User::query()->where('username', 'peneliti-sim')->firstOrFail())
            ->get('/riset/kelengkapan')->assertOk()->assertSee('Kelengkapan data');

        actingAs(User::query()->where('username', 'observer-sim-1')->firstOrFail())
            ->get('/observasi')->assertOk()->assertSee('Lembar observasi');
    });
});
