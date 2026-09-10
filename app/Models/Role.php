<?php

namespace App\Models;

use App\Enums\Peran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Peran: siswa | guru | validator | observer | peneliti | admin.
 * Daftar lengkapnya ada di enum {@see Peran}; tabel ini hanya cerminannya.
 */
#[Fillable(['nama', 'label'])]
class Role extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Ambil (atau buat) baris peran untuk sebuah nilai enum. Dipakai seeder,
     * factory, dan `User::berikanPeran()` supaya tidak ada yang mengetik nama
     * peran sebagai string lepas.
     */
    public static function untuk(Peran $peran): self
    {
        return static::query()->firstOrCreate(
            ['nama' => $peran->value],
            ['label' => $peran->label()],
        );
    }
}
