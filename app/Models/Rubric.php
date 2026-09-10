<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Rubrik analitik empat tingkat. `untuk`: produk_akhir | uraian_ct. */
#[Fillable(['nama', 'untuk'])]
class Rubric extends Model
{
    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriteria::class)->orderBy('id');
    }
}
