<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['questionnaire_id', 'aspek', 'urutan', 'pernyataan', 'butir_negatif'])]
class QuestionnaireItem extends Model
{
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['butir_negatif' => 'boolean'];
    }
}
