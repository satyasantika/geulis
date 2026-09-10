<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['validation_instrument_id', 'aspek', 'urutan', 'pernyataan'])]
class ValidationItem extends Model
{
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(ValidationInstrument::class, 'validation_instrument_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ValidationRating::class);
    }

    public function aikenResult(): HasOne
    {
        return $this->hasOne(AikenResult::class)->latestOfMany();
    }
}
