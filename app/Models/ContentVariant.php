<?php

namespace App\Models;

use Database\Factories\ContentVariantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentVariant extends Model
{
    /** @use HasFactory<ContentVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'lesson_unit_id', 'level', 'modus', 'judul', 'badan_konten',
        'cultural_asset_id', 'konfigurasi_geogebra', 'status',
    ];

    protected $casts = ['konfigurasi_geogebra' => 'array'];

    public function lessonUnit(): BelongsTo
    {
        return $this->belongsTo(LessonUnit::class);
    }

    public function culturalAsset(): BelongsTo
    {
        return $this->belongsTo(CulturalAsset::class);
    }

    /** Varian yang mungkin cocok untuk level & modus tertentu ('*' = berlaku semua). */
    public function scopeUntuk(Builder $q, string $level, string $modus): Builder
    {
        return $q->where(fn ($w) => $w->where('level', $level)->orWhere('level', '*'))
            ->where(fn ($w) => $w->where('modus', $modus)->orWhere('modus', '*'));
    }
}
