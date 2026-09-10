<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MasukRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * S-01 Masuk. Tipis: aturan masuk ada di {@see MasukRequest}.
 */
class LoginController extends Controller
{
    public function form(): View
    {
        return view('auth.masuk');
    }

    public function proses(MasukRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($user->rutePulang());
    }

    public function keluar(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }
}
