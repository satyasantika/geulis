<?php

namespace App\Filament\Pages;

use App\Services\Differentiation\DifferentiationConfig;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * A-03 Parameter adaptasi (α, ambang, batas remedial).
 *
 * Sengaja HANYA menampilkan, tidak menyunting. Nilainya tinggal di
 * config/geulis.php + .env supaya setiap perubahan tercatat di Git dan
 * tidak bisa diubah diam-diam dari peramban setelah validasi ahli dimulai.
 */
class PengaturanAdaptasi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Parameter adaptasi';

    protected static ?string $title = 'Parameter Mesin Diferensiasi';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.pengaturan-adaptasi';

    /**
     * @return array<string, array{nilai: mixed, arti: string}>
     */
    public function parameter(): array
    {
        $c = DifferentiationConfig::fromConfig();

        return [
            'GEULIS_ALPHA' => ['nilai' => $c->alpha, 'arti' => 'Bobot riwayat pada M_baru = α·M_lama + (1−α)·s'],
            'GEULIS_AMBANG_L2' => ['nilai' => $c->ambangL2, 'arti' => 'Skor kesiapan R minimal untuk level awal L2'],
            'GEULIS_AMBANG_L3' => ['nilai' => $c->ambangL3, 'arti' => 'Skor kesiapan R minimal untuk level awal L3'],
            'GEULIS_AMBANG_PROMOSI' => ['nilai' => $c->ambangPromosi, 'arti' => 'M ≥ nilai ini → RULE_PROMOTE / RULE_ENRICH'],
            'GEULIS_AMBANG_REMEDIAL' => ['nilai' => $c->ambangRemedial, 'arti' => 'M < nilai ini → RULE_REMEDIATE / RULE_ESCALATE'],
            'GEULIS_MAKS_REMEDIAL' => ['nilai' => $c->maksIterasiRemedial, 'arti' => 'Batas pengulangan remedial sebelum ditandai untuk guru'],
        ];
    }

    public function terkunci(): bool
    {
        return (bool) config('geulis.parameter_terkunci');
    }
}
