<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu QR = satu akun simulasi. `diklaim_pada` terisi saat pemiliknya masuk;
 * token yang sudah diklaim tidak bisa dipakai orang lain.
 */
#[Fillable(['simulation_run_id', 'user_id', 'token', 'urutan', 'diklaim_pada'])]
class SimulationLoginToken extends Model
{
    public function run(): BelongsTo
    {
        return $this->belongsTo(SimulationRun::class, 'simulation_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tautanMasuk(): string
    {
        return route('simulasi.masuk', $this->token);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diklaim_pada' => 'datetime',
        ];
    }
}
