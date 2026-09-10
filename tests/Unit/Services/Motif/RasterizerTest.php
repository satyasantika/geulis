<?php

use App\Services\Motif\Rasterizer;

it('fills the cells whose centres fall inside a polygon', function (): void {
    // Kisi 10 × 10 pada −5..5: satu sel = 1 satuan; persegi 0..2 menutupi 2 × 2 sel.
    $r = new Rasterizer(10, -5, 5);
    $raster = $r->raster([[[0, 0], [2, 0], [2, 2], [0, 2]]]);

    expect(array_sum(array_map('intval', $raster)))->toBe(4)
        ->and($raster[5 * 10 + 5])->toBeTrue()   // pusat (0,5, 0,5)
        ->and($raster[6 * 10 + 6])->toBeTrue()   // pusat (1,5, 1,5)
        ->and($raster[4 * 10 + 5])->toBeFalse();
});

it('clips polygons outside the canvas and ignores degenerate ones', function (): void {
    $r = new Rasterizer(10, -5, 5);

    expect(array_sum(array_map('intval', $r->raster([[[4, 4], [8, 4], [8, 8], [4, 8]]]))))->toBe(1)
        ->and(array_sum(array_map('intval', $r->raster([[[0, 0], [1, 1]]]))))->toBe(0);
});
