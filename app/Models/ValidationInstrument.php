<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Instrumen validasi ahli: aspek × butir, skala 1–c. */
#[Fillable(['nama', 'sasaran_validator', 'skala_maks'])]
class ValidationInstrument extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(ValidationItem::class)->orderBy('urutan');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ExpertValidation::class);
    }
}
