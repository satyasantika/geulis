<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Produk siswa (diferensiasi produk — bentuk dipilih siswa). Berkas disimpan
 * di disk `local` (privat) dan disajikan lewat rute berotorisasi.
 */
#[Fillable(['user_id', 'meeting_id', 'bentuk', 'berkas', 'tautan', 'deskripsi', 'dikirim_pada'])]
class Product extends Model
{
    public const array BENTUK = [
        'desain_motif' => ['label' => 'Desain Motif Orisinal', 'ikon' => '🎨', 'keterangan' => 'Rancang motif baru di Motif Builder + tuliskan algoritmanya.'],
        'laporan_algoritmik' => ['label' => 'Laporan Algoritmik', 'ikon' => '📄', 'keterangan' => 'Bedah motif artefak asli menjadi urutan transformasi bermatriks.'],
        'video' => ['label' => 'Video Penjelasan', 'ikon' => '🎬', 'keterangan' => 'Jelaskan cara membaca transformasi pada artefak, maks 3 menit (tautan).'],
        'poster' => ['label' => 'Poster Infografis', 'ikon' => '🖼', 'keterangan' => 'Petakan motif budaya Priangan Timur ke jenis transformasinya.'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function rubricScores(): HasMany
    {
        return $this->hasMany(RubricScore::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['dikirim_pada' => 'datetime'];
    }
}
