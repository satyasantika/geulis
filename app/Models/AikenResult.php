<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['validation_item_id', 'nilai_v', 'kategori', 'n_penilai', 'dihitung_pada'])]
class AikenResult extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(ValidationItem::class, 'validation_item_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['nilai_v' => 'float', 'dihitung_pada' => 'datetime'];
    }
}
