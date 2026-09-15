<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jawaban terbuka (kritik & saran) pada angket persepsi; `kode` menunjuk teks pertanyaan di config('angket.catatan_persepsi'). */
#[Fillable(['user_id', 'questionnaire_id', 'kode', 'jawaban'])]
class QuestionnaireReflection extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
