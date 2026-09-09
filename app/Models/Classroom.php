<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kelas (rombongan belajar). `kelompok_riset` melekat di sini, bukan pada
 * siswa — sesuai desain kuasi-eksperimen kelas utuh.
 */
class Classroom extends Model
{
    protected $fillable = ['school_id', 'guru_id', 'nama', 'tahun_ajaran', 'kelompok_riset', 'kode_gabung', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
