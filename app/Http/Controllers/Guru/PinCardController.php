<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * G-05 Cetak kartu PIN satu kelas — siap gunting, hitam-putih.
 */
class PinCardController extends Controller
{
    public function cetak(Request $request, Classroom $classroom): View
    {
        abort_unless($classroom->guru_id === $request->user()->id, 403);

        $siswa = $classroom->siswa()->wherePivot('status', 'aktif')->get();

        return view('guru.kartu-pin', [
            'kelas' => $classroom->load('school'),
            'siswa' => $siswa,
            'alamat' => rtrim(config('app.url'), '/').'/masuk',
        ]);
    }
}
