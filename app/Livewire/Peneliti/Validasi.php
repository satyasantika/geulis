<?php

namespace App\Livewire\Peneliti;

use App\Enums\Peran;
use App\Models\ExpertValidation;
use App\Models\User;
use App\Models\ValidationInstrument;
use App\Services\Research\RekapAiken;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * V-02 Rekap Aiken's V + pengelolaan undangan validator (tautan bertanda).
 * Surel tidak dikirim otomatis: tautan ditampilkan untuk disalin — sekolah
 * dan kampus sering memblokir SMTP, dan ahli lebih responsif lewat WhatsApp.
 */
#[Title("Validasi Ahli · Aiken's V")]
class Validasi extends Component
{
    public string $namaValidator = '';

    public string $emailValidator = '';

    public ?int $instrumenId = null;

    public function mount(): void
    {
        $this->instrumenId = ValidationInstrument::query()->value('id');
    }

    public function undang(): void
    {
        $data = $this->validate([
            'namaValidator' => 'required|string|max:255',
            'emailValidator' => 'nullable|email|max:255',
            'instrumenId' => 'required|exists:validation_instruments,id',
        ]);

        $validator = User::query()->firstOrCreate(
            ['username' => 'validator-'.Str::slug($data['namaValidator'])],
            ['nama' => $data['namaValidator'], 'email' => $data['emailValidator'] ?: null, 'password' => Str::random(32)],
        );
        $validator->berikanPeran(Peran::Validator);

        ExpertValidation::query()->firstOrCreate(
            ['validator_id' => $validator->getKey(), 'validation_instrument_id' => $data['instrumenId']],
        );

        $this->reset('namaValidator', 'emailValidator');
    }

    public static function tautan(ExpertValidation $v): string
    {
        return URL::signedRoute('validasi.form', ['token' => $v->token]);
    }

    public function render(RekapAiken $rekap): View
    {
        $instrumen = ValidationInstrument::query()->with('validations.validator')->find($this->instrumenId);

        return view('livewire.peneliti.validasi', [
            'instrumen' => $instrumen,
            'daftarInstrumen' => ValidationInstrument::query()->get(),
            'undangan' => $instrumen?->validations ?? collect(),
            'rekap' => $instrumen && $instrumen->validations->where('status', 'selesai')->isNotEmpty() ? $rekap->hitung($instrumen) : null,
        ]);
    }
}
