<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Masuk memakai NIS (siswa) atau NIP/inisial (guru) + PIN.
 *
 * Pembatasan laju (cetak biru §9): 5 percobaan per menit per pasangan
 * username+IP. PIN hanya 6 digit, jadi tanpa pembatasan ini tebak-tebakan
 * dari HP teman sekelas menjadi mudah.
 */
class MasukRequest extends FormRequest
{
    public const int MAKS_PERCOBAAN = 5;

    public const int JEDA_DETIK = 60;

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'pin' => ['required', 'string', 'max:72'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'NIS',
            'pin' => 'PIN',
        ];
    }

    public function authenticate(): User
    {
        $this->pastikanBelumTerkunci();

        $kredensial = [
            'username' => trim($this->string('username')->toString()),
            'password' => $this->string('pin')->toString(),
        ];

        if (! Auth::attempt($kredensial, remember: false)) {
            RateLimiter::hit($this->kunciLaju(), self::JEDA_DETIK);

            throw ValidationException::withMessages([
                'username' => 'NIS atau PIN belum cocok. Periksa kartu PIN dari gurumu.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Akun dinonaktifkan guru/admin (mis. pindah kelas). PIN-nya tetap
        // tersimpan, jadi ditolak di sini, bukan saat mencocokkan sandi.
        if (! $user->aktif) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun ini sedang tidak aktif. Hubungi gurumu.',
            ]);
        }

        RateLimiter::clear($this->kunciLaju());

        $user->forceFill(['terakhir_masuk_pada' => now()])->save();

        return $user;
    }

    protected function pastikanBelumTerkunci(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciLaju(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->kunciLaju());

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
        ]);
    }

    protected function kunciLaju(): string
    {
        return Str::transliterate(Str::lower($this->string('username')->toString()).'|'.$this->ip());
    }
}
