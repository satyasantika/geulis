<?php

namespace App\Models;

use Database\Factories\ReadinessItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Butir Tes Kesiapan Prasyarat (15 butir, empat prasyarat).
 */
#[Fillable(['urutan', 'pertanyaan', 'pilihan', 'kunci', 'prasyarat'])]
class ReadinessItem extends Model
{
    /** @use HasFactory<ReadinessItemFactory> */
    use HasFactory;

    public function responses(): HasMany
    {
        return $this->hasMany(ReadinessResponse::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['pilihan' => 'array', 'kunci' => 'integer'];
    }
}
