<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Angket respons untuk siswa atau guru; `jenis` membedakan kepraktisan dari persepsi. */
#[Fillable(['nama', 'sasaran', 'jenis', 'skala_maks', 'aktif'])]
class Questionnaire extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(QuestionnaireItem::class)->orderBy('urutan');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
