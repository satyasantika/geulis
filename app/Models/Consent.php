<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Persetujuan penelitian (S-02). Siswa yang menolak TETAP boleh belajar;
 * barisnya hanya menandai bahwa datanya dikeluarkan dari analisis.
 * `persetujuan_ortu_diterima` dicatat guru dari formulir kertas.
 */
#[Fillable(['user_id', 'setuju_data_penelitian', 'persetujuan_ortu_diterima', 'disetujui_pada'])]
class Consent extends Model
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
        return [
            'setuju_data_penelitian' => 'boolean',
            'persetujuan_ortu_diterima' => 'boolean',
            'disetujui_pada' => 'datetime',
        ];
    }
}
