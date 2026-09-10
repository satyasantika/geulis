<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keanggotaan siswa pada satu kelas, sekaligus posisi adaptifnya saat ini
 * (`level_kini`, `modus_kini`) yang diperbarui Mesin Diferensiasi.
 */
#[Fillable(['user_id', 'classroom_id', 'level_kini', 'modus_kini', 'status'])]
class Enrollment extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
