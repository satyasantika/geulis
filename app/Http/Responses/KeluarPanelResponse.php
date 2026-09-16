<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

/**
 * Setelah keluar dari panel admin, kembali ke beranda publik —
 * bukan ke /admin/login Filament.
 */
class KeluarPanelResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('beranda');
    }
}
