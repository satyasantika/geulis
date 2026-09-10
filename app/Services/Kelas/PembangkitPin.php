<?php

namespace App\Services\Kelas;

/**
 * PIN 6 digit acak untuk siswa. Menghindari pola yang mudah ditebak
 * (semua digit sama, urutan naik/turun) karena siswa cenderung membagikan
 * PIN "cantik" ke temannya.
 */
final class PembangkitPin
{
    public function baru(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->mudahDitebak($pin));

        return $pin;
    }

    public function mudahDitebak(string $pin): bool
    {
        if (count(array_unique(str_split($pin))) === 1) {
            return true;
        }

        return str_contains('01234567890123456789', $pin) || str_contains('98765432109876543210', $pin);
    }
}
