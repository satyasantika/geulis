<?php

/**
 * Parameter penelitian & Mesin Diferensiasi.
 *
 * Seluruh nilai di sini ikut divalidasi ahli dan WAJIB dilaporkan apa adanya
 * di bagian metode artikel. Jangan mengubahnya setelah validasi ahli dimulai.
 */
return [

    // --- Mesin Diferensiasi
    'alpha' => env('GEULIS_ALPHA', 0.4),
    'ambang_l2' => env('GEULIS_AMBANG_L2', 60),
    'ambang_l3' => env('GEULIS_AMBANG_L3', 80),
    'ambang_promosi' => env('GEULIS_AMBANG_PROMOSI', 0.80),
    'ambang_remedial' => env('GEULIS_AMBANG_REMEDIAL', 0.50),
    'maks_iterasi_remedial' => env('GEULIS_MAKS_REMEDIAL', 2),

    // --- Motif Builder
    'motif_resolusi' => 200,   // kisi raster untuk penskoran IoU
    'motif_ambang_lolos' => 90.0,  // persen kemiripan

    // --- Instrumen penelitian
    'aiken' => [
        'skala_maks' => 5,
        'skor_terendah' => 1,
        'kategori' => [[0.80, 'tinggi'], [0.40, 'sedang'], [0.00, 'rendah']],
    ],
    'ngain' => [
        'skor_maks' => 100.0,
        'kategori' => [[0.70, 'tinggi'], [0.30, 'sedang'], [0.00, 'rendah']],
    ],

    // --- Etika & privasi
    'anonimkan_ekspor' => true,   // JANGAN diubah ke false
    'retensi_data_tahun' => 5,
];
