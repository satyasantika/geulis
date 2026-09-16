<?php

namespace App\Http\Controllers\Simulasi;

use App\Http\Controllers\Controller;
use App\Models\SimulationLoginToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Masuk simulasi lewat QR: GET menampilkan konfirmasi (aman dari prefetch),
 * POST mengklaim token. Token yang sudah diklaim tidak bisa dipakai orang lain.
 */
class MasukSimulasiController extends Controller
{
    public function form(string $token): View|RedirectResponse|Response
    {
        $baris = $this->token($token);

        if ($baris->diklaim_pada !== null) {
            if (auth()->id() === $baris->user_id) {
                return redirect($baris->user->rutePulang());
            }

            return response()->view('simulasi.qr-sudah-dipakai', ['token' => $baris], 410);
        }

        $baris->load('user.roles');

        return view('simulasi.masuk', ['token' => $baris]);
    }

    public function proses(Request $request, string $token): RedirectResponse|Response
    {
        $kunci = 'simulasi-masuk:'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 20)) {
            abort(429, 'Terlalu banyak percobaan. Tunggu sebentar.');
        }
        RateLimiter::hit($kunci, 60);

        $user = DB::transaction(function () use ($token) {
            $baris = SimulationLoginToken::query()->where('token', $token)->lockForUpdate()->firstOrFail();

            if ($baris->diklaim_pada !== null) {
                return null;
            }

            $baris->forceFill(['diklaim_pada' => now()])->save();
            $baris->user->forceFill(['terakhir_masuk_pada' => now()])->save();

            return $baris->user;
        });

        if ($user === null) {
            return response()->view('simulasi.qr-sudah-dipakai', ['token' => $this->token($token)], 410);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($user->rutePulang());
    }

    private function token(string $token): SimulationLoginToken
    {
        return SimulationLoginToken::query()->where('token', $token)->with('user.roles')->firstOrFail();
    }
}
