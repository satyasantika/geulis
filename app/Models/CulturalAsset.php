<?php

namespace App\Models;

use App\Exceptions\AsetBudayaTanpaAtribusi;
use Database\Factories\CulturalAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aset budaya (foto/video artefak) beserta atribusinya.
 *
 * Aturan #6: setiap aset WAJIB punya perajin_sumber, lokasi,
 * tanggal_dokumentasi, dan izin_diperoleh = true. Ditegakkan di sini, di
 * lapisan model, supaya seeder, impor, atau tinker pun tidak bisa melewatinya.
 */
#[Fillable(['nama_motif', 'artefak', 'perajin_sumber', 'lokasi', 'tanggal_dokumentasi', 'izin_diperoleh', 'berkas', 'catatan_matematis'])]
class CulturalAsset extends Model
{
    /** @use HasFactory<CulturalAssetFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $aset): void {
            foreach (['perajin_sumber', 'lokasi', 'tanggal_dokumentasi'] as $kolom) {
                if (blank($aset->{$kolom})) {
                    throw new AsetBudayaTanpaAtribusi("Aset budaya wajib punya {$kolom}.");
                }
            }

            if (! $aset->izin_diperoleh) {
                throw new AsetBudayaTanpaAtribusi('Aset budaya tidak boleh disimpan sebelum izin dari perajin/pemilik diperoleh.');
            }
        });
    }

    public function contentVariants(): HasMany
    {
        return $this->hasMany(ContentVariant::class);
    }

    /** Baris atribusi yang ditampilkan di layar siswa. */
    public function atribusi(): string
    {
        return sprintf('%s · %s · %s · izin diperoleh', $this->perajin_sumber, $this->lokasi, $this->tanggal_dokumentasi->translatedFormat('j F Y'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['tanggal_dokumentasi' => 'date', 'izin_diperoleh' => 'boolean'];
    }
}
