<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rekap skor tes CT per siswa. Baris dibuat saat siswa MULAI (created_at =
 * waktu mulai, dasar pewaktu); `dihitung_pada` terisi saat seluruh butir —
 * termasuk uraian yang dinilai guru — sudah punya skor.
 */
#[Fillable(['user_id', 'ct_test_id', 'skor_d', 'skor_p', 'skor_a', 'skor_al', 'skor_total', 'persen', 'dihitung_pada', 'dikumpulkan_pada'])]
class CtScore extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(CtTest::class, 'ct_test_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skor_d' => 'float', 'skor_p' => 'float', 'skor_a' => 'float', 'skor_al' => 'float',
            'skor_total' => 'float', 'persen' => 'float',
            'dihitung_pada' => 'datetime', 'dikumpulkan_pada' => 'datetime',
        ];
    }
}
