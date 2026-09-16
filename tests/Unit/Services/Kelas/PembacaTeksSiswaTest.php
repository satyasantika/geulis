<?php

use App\Services\Kelas\PembacaTeksSiswa;

it('reads NIS then name with tab, comma, or space', function (string $teks): void {
    $hasil = (new PembacaTeksSiswa)->baca($teks);

    expect($hasil['galat'])->toBe([])
        ->and($hasil['baris'])->toBe([
            ['nama' => 'Reza Pratama', 'nis' => '0056781234', 'jenis_kelamin' => null],
            ['nama' => 'Siti Aminah', 'nis' => '0056781235', 'jenis_kelamin' => null],
        ]);
})->with([
    'tab' => "0056781234\tReza Pratama\n0056781235\tSiti Aminah\n",
    'koma' => "0056781234,Reza Pratama\n0056781235,Siti Aminah\n",
    'spasi' => "0056781234 Reza Pratama\n0056781235 Siti Aminah\n",
    'nama lalu NIS' => "Reza Pratama 0056781234\nSiti Aminah,0056781235\n",
]);

it('skips a header row and ignores gender or school-code columns', function (): void {
    $teks = "NIS\tnama\nL\t0056781234\tsmp5contoh\tReza Pratama\nP,0056781235,Siti Aminah\n";

    $hasil = (new PembacaTeksSiswa)->baca($teks);

    expect($hasil['galat'])->toBe([])
        ->and($hasil['baris'][0]['nama'])->toBe('Reza Pratama')
        ->and($hasil['baris'][0]['nis'])->toBe('0056781234')
        ->and($hasil['baris'][1]['nama'])->toBe('Siti Aminah');
});

it('reports a line without both NIS and name, and a duplicate NIS', function (): void {
    $hasil = (new PembacaTeksSiswa)->baca("hanya nama\n0056781234 Reza\n0056781234 Siti\n");

    expect($hasil['baris'])->toHaveCount(1)
        ->and($hasil['galat'])->toBe([
            'Baris 1: tulis NIS dan nama (contoh: 0056781234 Reza Pratama).',
            'Baris 3: NIS 0056781234 muncul dua kali di daftar.',
        ]);
});
