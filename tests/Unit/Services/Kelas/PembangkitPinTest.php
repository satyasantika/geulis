<?php

use App\Services\Kelas\PembangkitPin;

it('produces six digits that are not an obvious pattern', function (): void {
    $pembangkit = new PembangkitPin;

    foreach (range(1, 50) as $i) {
        $pin = $pembangkit->baru();

        expect($pin)->toMatch('/^\d{6}$/')
            ->and($pembangkit->mudahDitebak($pin))->toBeFalse();
    }
});

it('flags repeated and sequential PINs as guessable', function (string $pin): void {
    expect((new PembangkitPin)->mudahDitebak($pin))->toBeTrue();
})->with(['000000', '111111', '123456', '654321', '456789', '901234']);
