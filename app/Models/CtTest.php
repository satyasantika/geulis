<?php

namespace App\Models;

use Database\Factories\CtTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Tes CT pra/pasca — instrumen utama N-Gain. */
#[Fillable(['jenis', 'judul', 'durasi_menit', 'aktif'])]
class CtTest extends Model
{
    /** @use HasFactory<CtTestFactory> */
    use HasFactory;

    public function items(): HasMany
    {
        return $this->hasMany(CtItem::class)->orderBy('urutan');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CtScore::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
