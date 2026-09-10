<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Siswa yang belum menjawab S-02 diarahkan ke sana lebih dahulu.
 * Jawabannya boleh "tidak" — yang penting sudah dijawab.
 */
class EnsureConsentRecorded
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->consent === null) {
            return redirect()->route('siswa.persetujuan');
        }

        return $next($request);
    }
}
