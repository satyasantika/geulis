<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Satu undangan validasi untuk satu validator: token unik pada tautan
 * bertanda — validator tidak perlu membuat akun.
 */
#[Fillable(['validator_id', 'validation_instrument_id', 'token', 'status', 'selesai_pada'])]
class ExpertValidation extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $v): void {
            $v->token ??= Str::random(64);
        });
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(ValidationInstrument::class, 'validation_instrument_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ValidationRating::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['selesai_pada' => 'datetime'];
    }
}
