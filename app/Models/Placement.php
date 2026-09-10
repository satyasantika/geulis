<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil penempatan awal oleh Mesin Diferensiasi. `penjelasan` menyimpan
 * aturan dan parameter yang dipakai — untuk audit validator dan artikel.
 */
#[Fillable(['user_id', 'skor_readiness', 'level_awal', 'modus', 'artefak_utama', 'penjelasan'])]
class Placement extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['penjelasan' => 'array'];
    }
}
