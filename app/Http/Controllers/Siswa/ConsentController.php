<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * S-02 Persetujuan penelitian. Ditanyakan sekali pada masuk pertama.
 * Menolak tidak menutup akses belajar; hanya menandai data.
 */
class ConsentController extends Controller
{
    public function form(Request $request): View
    {
        return view('siswa.persetujuan', [
            'consent' => $request->user()->consent,
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'setuju' => ['required', 'in:ya,tidak'],
        ], [
            'setuju.required' => 'Pilih salah satu dulu, ya.',
        ]);

        $request->user()->consent()->updateOrCreate([], [
            'setuju_data_penelitian' => $data['setuju'] === 'ya',
            'disetujui_pada' => now(),
        ]);

        return redirect()->route('siswa.jalur');
    }
}
