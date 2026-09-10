<?php

use App\Enums\Peran;
use App\Http\Requests\Auth\MasukRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('S-01 masuk', function (): void {
    it('renders the login form for guests', function (): void {
        get('/masuk')
            ->assertOk()
            ->assertSee('NIS')
            ->assertSee('PIN (6 digit)');
    });

    it('signs a student in with NIS and PIN and sends them to the learning path', function (): void {
        $siswa = User::factory()->siswa()->pin('482913')->create(['username' => '0056781234']);

        post('/masuk', ['username' => '0056781234', 'pin' => '482913'])
            ->assertRedirect(route('siswa.jalur'));

        assertAuthenticatedAs($siswa);
        expect($siswa->fresh()->terakhir_masuk_pada)->not->toBeNull();
    });

    it('sends a teacher to the teacher home after signing in', function (): void {
        $guru = User::factory()->guru()->create(['username' => 'guru-1']);

        post('/masuk', ['username' => 'guru-1', 'pin' => 'password'])
            ->assertRedirect(route('guru.beranda'));

        assertAuthenticatedAs($guru);
    });

    it('sends an admin to the Filament panel after signing in', function (): void {
        User::factory()->admin()->create(['username' => 'admin']);

        post('/masuk', ['username' => 'admin', 'pin' => 'password'])
            ->assertRedirect('/admin');
    });

    it('rejects a wrong PIN without revealing which field is wrong', function (): void {
        User::factory()->siswa()->pin('482913')->create(['username' => '0056781234']);

        post('/masuk', ['username' => '0056781234', 'pin' => '000000'])
            ->assertSessionHasErrors(['username' => 'NIS atau PIN belum cocok. Periksa kartu PIN dari gurumu.']);

        assertGuest();
    });

    it('rejects an unknown NIS with the same message', function (): void {
        post('/masuk', ['username' => '9999999999', 'pin' => '482913'])
            ->assertSessionHasErrors(['username' => 'NIS atau PIN belum cocok. Periksa kartu PIN dari gurumu.']);

        assertGuest();
    });

    it('rejects an inactive account even with the right PIN', function (): void {
        User::factory()->siswa()->nonaktif()->pin('482913')->create(['username' => '0056781234']);

        post('/masuk', ['username' => '0056781234', 'pin' => '482913'])
            ->assertSessionHasErrors(['username' => 'Akun ini sedang tidak aktif. Hubungi gurumu.']);

        assertGuest();
    });

    it('requires both NIS and PIN', function (): void {
        post('/masuk', [])
            ->assertSessionHasErrors(['username', 'pin']);

        assertGuest();
    });

    it('locks the account after too many failed attempts', function (): void {
        User::factory()->siswa()->pin('482913')->create(['username' => '0056781234']);

        foreach (range(1, MasukRequest::MAKS_PERCOBAAN) as $i) {
            post('/masuk', ['username' => '0056781234', 'pin' => '000000']);
        }

        post('/masuk', ['username' => '0056781234', 'pin' => '482913'])
            ->assertSessionHasErrorsIn('default', ['username']);

        expect(session('errors')->first('username'))->toStartWith('Terlalu banyak percobaan.');
        assertGuest();
    });

    it('redirects a signed-in user away from the login form', function (): void {
        $siswa = User::factory()->siswa()->create();

        actingAs($siswa)->get('/masuk')->assertRedirect(route('siswa.jalur'));
    });

    it('signs the user out', function (): void {
        $siswa = User::factory()->siswa()->create();

        actingAs($siswa)->post('/keluar')->assertRedirect(route('masuk'));

        assertGuest();
    });
});

describe('beranda', function (): void {
    it('shows the landing page to guests', function (): void {
        get('/')
            ->assertOk()
            ->assertSee('GEULIS')
            ->assertSee('Delapan kelopak')
            ->assertSee('Masuk belajar')
            ->assertSee('Depi Ardian Nugraha');
        get('/belajar')->assertRedirect(route('masuk'));
    });

    it('sends each role to its own home', function (Closure $factory, string $tujuan): void {
        $user = $factory()->create();

        actingAs($user)->get('/')->assertRedirect($tujuan);
    })->with([
        'siswa' => [fn () => User::factory()->siswa(), '/belajar'],
        'guru' => [fn () => User::factory()->guru(), '/guru'],
        'peneliti' => [fn () => User::factory()->peneliti(), '/riset/kelengkapan'],
        'admin' => [fn () => User::factory()->admin(), '/admin'],
    ]);

    it('lets a student see their empty learning path at 360 px', function (): void {
        $siswa = User::factory()->siswa()->create(['nama' => 'Reza']);
        $siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);

        actingAs($siswa)->get('/belajar')
            ->assertOk()
            ->assertSee('Halo, Reza')
            ->assertSee('width=device-width', escape: false);
    });

    it('records more than one role on one account', function (): void {
        $user = User::factory()->create();

        $user->berikanPeran(Peran::Admin, Peran::Peneliti);
        $user->berikanPeran(Peran::Admin);

        expect($user->roles()->count())->toBe(2)
            ->and($user->punyaPeran(Peran::Admin))->toBeTrue()
            ->and($user->punyaPeran(Peran::Siswa))->toBeFalse();
    });
});
