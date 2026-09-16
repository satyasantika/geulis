<?php

use App\Enums\Peran;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('lets an admin paste users and creates accounts without duplicating usernames', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callAction('imporMassal', [
            'daftar' => "siswa,0056781234,Reza Pratama,siswa-1234\nguru,197812312345,Vepi Nurhasanah,password\n",
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $reza = User::query()->where('username', '0056781234')->firstOrFail();
    $vepi = User::query()->where('username', '197812312345')->firstOrFail();

    expect($reza->punyaPeran(Peran::Siswa))->toBeTrue()
        ->and($reza->kode_anonim)->toBe('S-001')
        ->and(Hash::check('siswa-1234', $reza->password))->toBeTrue()
        ->and($reza->pin_kartu)->toBe('siswa-1234')
        ->and($vepi->punyaPeran(Peran::Guru))->toBeTrue()
        ->and($vepi->kode_anonim)->toBeNull()
        ->and(Hash::check('password', $vepi->password))->toBeTrue();
});

it('adds a role to an existing username instead of creating a second user', function (): void {
    $admin = User::factory()->admin()->create();
    $lama = User::factory()->siswa()->pin('482913')->create([
        'username' => '0056781234',
        'nama' => 'Reza Pratama',
        'kode_anonim' => 'S-007',
        'pin_kartu' => '482913',
    ]);

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callAction('imporMassal', [
            'daftar' => "guru,0056781234,Reza Pratama,password-baru\nsiswa,0056781299,Siti Aminah,siswa-1234\n",
        ])
        ->assertHasNoActionErrors();

    $lama->refresh();

    expect(User::query()->where('username', '0056781234')->count())->toBe(1)
        ->and($lama->punyaPeran(Peran::Siswa))->toBeTrue()
        ->and($lama->punyaPeran(Peran::Guru))->toBeTrue()
        ->and(Hash::check('482913', $lama->password))->toBeTrue()
        ->and($lama->kode_anonim)->toBe('S-007')
        ->and(User::query()->where('username', '0056781299')->value('kode_anonim'))->toBe('S-008');
});

it('keeps teachers out of the user list', function (): void {
    actingAs(User::factory()->guru()->create())
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();
});
