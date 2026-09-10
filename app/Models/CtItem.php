<?php

namespace App\Models;

use Database\Factories\CtItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Butir tes CT. `indikator`: D dekomposisi, P pengenalan pola, A abstraksi,
 * Al algoritma. `tipe`: pg (otomatis), isian (otomatis, cocok teks), uraian
 * (manual dengan `rubrik_butir` deskriptor 0–4).
 */
#[Fillable(['ct_test_id', 'urutan', 'indikator', 'stimulus', 'cultural_asset_id', 'pertanyaan', 'tipe', 'pilihan', 'kunci', 'rubrik_butir', 'skor_maks'])]
class CtItem extends Model
{
    /** @use HasFactory<CtItemFactory> */
    use HasFactory;

    public const array INDIKATOR = ['D' => 'Dekomposisi', 'P' => 'Pengenalan pola', 'A' => 'Abstraksi', 'Al' => 'Algoritma'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(CtTest::class, 'ct_test_id');
    }

    public function culturalAsset(): BelongsTo
    {
        return $this->belongsTo(CulturalAsset::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CtResponse::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['pilihan' => 'array', 'rubrik_butir' => 'array'];
    }
}
