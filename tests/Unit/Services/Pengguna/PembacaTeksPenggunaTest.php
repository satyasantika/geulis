<?php

use App\Enums\Peran;
use App\Services\Pengguna\PembacaTeksPengguna;

it('reads role, username, name, and password with comma, tab, or spaces', function (string $teks): void {
    $hasil = (new PembacaTeksPengguna)->baca($teks);

    expect($hasil['galat'])->toBe([])
        ->and($hasil['baris'])->toHaveCount(2)
        ->and($hasil['baris'][0]['peran'])->toBe(Peran::Siswa)
        ->and($hasil['baris'][0]['username'])->toBe('0056781234')
        ->and($hasil['baris'][0]['nama'])->toBe('Reza Pratama')
        ->and($hasil['baris'][0]['password'])->toBe('siswa-1234')
        ->and($hasil['baris'][1]['peran'])->toBe(Peran::Guru)
        ->and($hasil['baris'][1]['username'])->toBe('197812312345')
        ->and($hasil['baris'][1]['nama'])->toBe('Vepi Nurhasanah')
        ->and($hasil['baris'][1]['password'])->toBe('password');
})->with([
    'koma' => "siswa,0056781234,Reza Pratama,siswa-1234\nguru,197812312345,Vepi Nurhasanah,password\n",
    'tab' => "siswa\t0056781234\tReza Pratama\tsiswa-1234\nguru\t197812312345\tVepi Nurhasanah\tpassword\n",
    'spasi' => "siswa 0056781234 Reza Pratama siswa-1234\nguru 197812312345 Vepi Nurhasanah password\n",
    'NIP bertitik' => "siswa,0056781234,Reza Pratama,siswa-1234\nguru,1978.1231.2345,Vepi Nurhasanah,password\n",
]);

it('skips a header row', function (): void {
    $hasil = (new PembacaTeksPengguna)->baca("role,username,nama,password\nsiswa,0056781234,Reza Pratama,siswa-1234\n");

    expect($hasil['galat'])->toBe([])
        ->and($hasil['baris'])->toHaveCount(1)
        ->and($hasil['baris'][0]['username'])->toBe('0056781234');
});

it('reports an unknown role, a short row, and a duplicate username', function (): void {
    $hasil = (new PembacaTeksPengguna)->baca("kepala,001,Nama,sandi\nhanya tiga kolom\nsiswa,0056781234,Reza,siswa-1234\nsiswa,0056781234,Siti,siswa-1234\n");

    expect($hasil['baris'])->toHaveCount(1)
        ->and($hasil['galat'])->toBe([
            "Baris 1: peran 'kepala' tidak dikenal. Gunakan siswa, guru, validator, observer, peneliti, atau admin.",
            'Baris 2: tulis role, username, nama, password (contoh: siswa,0056781234,Reza Pratama,siswa-1234).',
            'Baris 4: username 0056781234 muncul dua kali di daftar.',
        ]);
});
