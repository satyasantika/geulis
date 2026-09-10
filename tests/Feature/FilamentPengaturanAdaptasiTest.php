<?php

use App\Filament\Pages\PengaturanAdaptasi;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('shows the research parameters read-only with the lock warning', function (): void {
    config(['geulis.parameter_terkunci' => false]);

    actingAs(User::factory()->peneliti()->create())
        ->get(PengaturanAdaptasi::getUrl())
        ->assertOk()
        ->assertSee('GEULIS_ALPHA')
        ->assertSee('0.4')
        ->assertSee('dikunci begitu validasi ahli dimulai');
});

it('shouts when the parameters are locked', function (): void {
    config(['geulis.parameter_terkunci' => true]);

    actingAs(User::factory()->admin()->create())
        ->get(PengaturanAdaptasi::getUrl())
        ->assertOk()
        ->assertSee('TERKUNCI');
});
