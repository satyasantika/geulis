<?php

use App\Enums\Peran;
use App\Filament\Resources\SimulationRuns\Pages\CreateSimulationRun;
use App\Filament\Resources\SimulationRuns\SimulationRunResource;
use App\Livewire\Simulasi\LayarQr;
use App\Models\Classroom;
use App\Models\SimulationLoginToken;
use App\Models\SimulationRun;
use App\Models\User;
use App\Services\Simulasi\PembuatSimulasi;
use App\Services\Simulasi\PenghapusSimulasi;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('lets an admin create a disposable simulation with classes, teachers, and students', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CreateSimulationRun::class)
        ->fillForm([
            'jumlah_kelas' => 2,
            'jumlah_guru' => 2,
            'jumlah_siswa' => 4,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $run = SimulationRun::query()->firstOrFail();

    expect($run->jumlah_kelas)->toBe(2)
        ->and($run->classrooms()->count())->toBe(2)
        ->and($run->users()->count())->toBe(6)
        ->and($run->tokens()->count())->toBe(6)
        ->and(Classroom::query()->where('simulation_run_id', $run->id)->value('kelompok_riset'))->toBe('non_riset');

    $guru = User::query()->where('simulation_run_id', $run->id)->whereHas('roles', fn ($q) => $q->where('nama', Peran::Guru->value))->firstOrFail();
    $siswa = User::query()->where('simulation_run_id', $run->id)->whereHas('roles', fn ($q) => $q->where('nama', Peran::Siswa->value))->firstOrFail();

    expect($guru->punyaPeran(Peran::Guru))->toBeTrue()
        ->and($siswa->punyaPeran(Peran::Siswa))->toBeTrue()
        ->and($siswa->consent)->not->toBeNull()
        ->and($siswa->consent->setuju_data_penelitian)->toBeFalse()
        ->and($siswa->kode_anonim)->toStartWith('T-');
});

it('logs a participant in from the QR confirmation and then refuses the same token for someone else', function (): void {
    $admin = User::factory()->admin()->create();
    $run = app(PembuatSimulasi::class)->buat(1, 1, 1, $admin);
    $token = $run->tokenBerikutnya();

    get(route('simulasi.masuk', $token->token))
        ->assertOk()
        ->assertSee($token->user->nama);

    post(route('simulasi.masuk.proses', $token->token))
        ->assertRedirect(route('guru.beranda'));

    expect($token->fresh()->diklaim_pada)->not->toBeNull();

    auth()->logout();

    get(route('simulasi.masuk', $token->token))->assertStatus(410);
    post(route('simulasi.masuk.proses', $token->token))->assertStatus(410);
    assertGuest();
});

it('shows the next QR after the previous participant has signed in', function (): void {
    $admin = User::factory()->admin()->create();
    $run = app(PembuatSimulasi::class)->buat(1, 1, 1, $admin);
    $pertama = $run->tokens()->orderBy('urutan')->firstOrFail();
    $kedua = $run->tokens()->orderBy('urutan')->skip(1)->firstOrFail();

    Livewire::test(LayarQr::class, ['layarToken' => $run->layar_token])
        ->assertSee('Guru Simulasi 1')
        ->assertDontSee('Siswa Simulasi 1');

    post(route('simulasi.masuk.proses', $pertama->token))->assertRedirect();

    Livewire::test(LayarQr::class, ['layarToken' => $run->layar_token])
        ->assertSee('Siswa Simulasi 1')
        ->assertDontSee('Guru Simulasi 1');
});

it('deletes simulation classes and users without leaving accounts behind', function (): void {
    $admin = User::factory()->admin()->create();
    $run = app(PembuatSimulasi::class)->buat(1, 1, 2, $admin);
    $id = $run->id;

    app(PenghapusSimulasi::class)->hapus($run);

    expect(SimulationRun::query()->whereKey($id)->exists())->toBeFalse()
        ->and(User::query()->where('simulation_run_id', $id)->exists())->toBeFalse()
        ->and(Classroom::query()->where('simulation_run_id', $id)->exists())->toBeFalse()
        ->and(SimulationLoginToken::query()->count())->toBe(0);
});

it('hides the simulation trigger from researchers and teachers', function (): void {
    actingAs(User::factory()->peneliti()->create())
        ->get(SimulationRunResource::getUrl('index'))
        ->assertForbidden();

    actingAs(User::factory()->guru()->create())
        ->get(SimulationRunResource::getUrl('index'))
        ->assertForbidden();
});
