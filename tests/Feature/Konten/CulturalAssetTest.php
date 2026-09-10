<?php

use App\Exceptions\AsetBudayaTanpaAtribusi;
use App\Filament\Resources\CulturalAssets\Pages\CreateCulturalAsset;
use App\Models\CulturalAsset;
use App\Models\User;
use Livewire\Livewire;

it('refuses to save an asset without permission from the artisan', function (): void {
    expect(fn () => CulturalAsset::factory()->tanpaIzin()->create())->toThrow(AsetBudayaTanpaAtribusi::class);
    expect(CulturalAsset::query()->count())->toBe(0);
});

it('refuses to save an asset missing an attribution field', function (string $kolom): void {
    expect(fn () => CulturalAsset::factory()->create([$kolom => '']))->toThrow(AsetBudayaTanpaAtribusi::class, $kolom);
})->with(['perajin_sumber', 'lokasi']);

it('saves a fully attributed asset and renders its attribution line', function (): void {
    $aset = CulturalAsset::factory()->create(['perajin_sumber' => 'Ibu Enok S.', 'lokasi' => 'Cigeureung, Kota Tasikmalaya', 'tanggal_dokumentasi' => '2026-05-12']);

    expect($aset->atribusi())->toContain('Ibu Enok S.')->toContain('Cigeureung')->toContain('izin diperoleh');
});

it('rejects the admin form when the permission box is not ticked', function (): void {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(CreateCulturalAsset::class)
        ->fillForm([
            'nama_motif' => 'Sawoan',
            'artefak' => 'batik',
            'perajin_sumber' => 'Ibu Enok S.',
            'lokasi' => 'Cigeureung',
            'tanggal_dokumentasi' => '2026-05-12',
            'izin_diperoleh' => false,
            'catatan_matematis' => 'Simetri lipat.',
        ])
        ->call('create')
        ->assertHasFormErrors(['izin_diperoleh']);

    expect(CulturalAsset::query()->count())->toBe(0);
});
