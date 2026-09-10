<?php

use App\Services\Kelas\PembacaCsvSiswa;

it('reads rows with a header in any column order and either separator', function (string $csv): void {
    $hasil = (new PembacaCsvSiswa)->baca($csv);

    expect($hasil['galat'])->toBe([])
        ->and($hasil['baris'])->toBe([
            ['nama' => 'Reza Pratama', 'nis' => '0056781234', 'jenis_kelamin' => 'L'],
            ['nama' => 'Siti Aminah', 'nis' => '0056781235', 'jenis_kelamin' => 'P'],
        ]);
})->with([
    'koma' => "nama,nis,jenis_kelamin\nReza Pratama,0056781234,L\nSiti Aminah,0056781235,P\n",
    'titik koma dari Excel' => "\xEF\xBB\xBFnis;jenis_kelamin;nama\r\n0056781234;Laki-laki;Reza Pratama\r\n0056781235;Perempuan;Siti Aminah\r\n",
    'judul JK' => "Nama,NIS,JK\n\"Reza Pratama\",\"0056781234\",L\nSiti Aminah,0056781235,P\n",
]);

it('assumes nama;nis;jenis_kelamin when there is no header', function (): void {
    $hasil = (new PembacaCsvSiswa)->baca("Reza Pratama;0056781234;L\nSiti Aminah;0056781235;\n");

    expect($hasil['baris'])->toHaveCount(2)
        ->and($hasil['baris'][1]['jenis_kelamin'])->toBeNull();
});

it('reports rows without a name or NIS and duplicate NIS by line number', function (): void {
    $hasil = (new PembacaCsvSiswa)->baca("nama,nis,jenis_kelamin\n,0056781234,L\nReza,0056781235,L\nBudi,0056781235,L\n");

    expect($hasil['baris'])->toHaveCount(1)
        ->and($hasil['galat'])->toBe([
            'Baris 2: nama dan NIS wajib diisi.',
            'Baris 4: NIS 0056781235 muncul dua kali di berkas.',
        ]);
});

it('keeps leading zeros and strips stray characters from a NIS', function (): void {
    $hasil = (new PembacaCsvSiswa)->baca("nama,nis,jenis_kelamin\nReza,'0056 781-234,L\n");

    expect($hasil['baris'][0]['nis'])->toBe('0056781234');
});
