<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sekolah mitra penelitian (tiga sekolah di Priangan Timur).
 */
class School extends Model
{
    protected $fillable = ['nama', 'npsn', 'kabupaten_kota', 'alamat', 'kontak_guru'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
