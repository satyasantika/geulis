<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'ct_item_id', 'jawaban', 'skor_otomatis', 'skor_manual', 'penilai_id', 'catatan_penilai', 'durasi_detik'])]
class CtResponse extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CtItem::class, 'ct_item_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    /** Skor yang berlaku: manual (uraian) bila ada, selain itu otomatis. */
    public function skorBerlaku(): ?float
    {
        return $this->skor_manual ?? $this->skor_otomatis;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['skor_otomatis' => 'float', 'skor_manual' => 'float'];
    }
}
