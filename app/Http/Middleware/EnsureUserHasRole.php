<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Otorisasi berbasis peran pada setiap rute (cetak biru §9). Dipakai sebagai
 * `role:guru` atau `role:admin,peneliti` — koma berarti "salah satu".
 * Selalu dipasang setelah `auth`; pengguna tanpa sesi ditangani di sana.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->punyaPeran(...$peran)) {
            abort(403, 'Halaman ini bukan untuk peranmu.');
        }

        return $next($request);
    }
}
