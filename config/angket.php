<?php

/**
 * Butir Angket Profil Belajar (20) dan Angket Minat Konteks Budaya (6).
 *
 * Disimpan sebagai berkas konfigurasi, bukan tabel, karena butirnya tetap
 * selama penelitian dan ikut divalidasi ahli bersama instrumen lain —
 * riwayat Git menjadi jejak revisinya. Skala Likert 1–4.
 */
return [

    'skala' => [1 => 'Sangat tidak setuju', 2 => 'Tidak setuju', 3 => 'Setuju', 4 => 'Sangat setuju'],

    // Profil belajar: 7 visual, 7 simbolik, 6 naratif. Urutan diselang-seling
    // agar siswa tidak menebak "kelompok" pernyataan.
    'profil' => [
        ['modus' => 'visual', 'teks' => 'Saya lebih cepat paham kalau melihat gambar atau animasi.'],
        ['modus' => 'simbolik', 'teks' => 'Saya nyaman bekerja dengan rumus dan simbol.'],
        ['modus' => 'naratif', 'teks' => 'Saya lebih paham kalau ada cerita atau contoh dari kehidupan sehari-hari.'],
        ['modus' => 'visual', 'teks' => 'Saya suka menggeser-geser bangun di layar untuk melihat apa yang berubah.'],
        ['modus' => 'simbolik', 'teks' => 'Saya suka mengubah masalah menjadi persamaan.'],
        ['modus' => 'naratif', 'teks' => 'Saya suka mendengar penjelasan lisan sebelum mencoba sendiri.'],
        ['modus' => 'visual', 'teks' => 'Saya membayangkan bentuk di kepala sebelum menghitung.'],
        ['modus' => 'simbolik', 'teks' => 'Saya lebih percaya hasil kalau sudah dihitung dengan angka.'],
        ['modus' => 'naratif', 'teks' => 'Saya lebih ingat konsep kalau tahu asal-usul atau kegunaannya.'],
        ['modus' => 'visual', 'teks' => 'Diagram dan sketsa membantu saya mengingat rumus.'],
        ['modus' => 'simbolik', 'teks' => 'Saya senang mencari pola dalam deretan angka.'],
        ['modus' => 'naratif', 'teks' => 'Menjelaskan ke teman dengan kata-kata membantu saya memahami.'],
        ['modus' => 'visual', 'teks' => 'Saya senang mewarnai atau menggambar pola.'],
        ['modus' => 'simbolik', 'teks' => 'Menuliskan langkah-langkah secara runtut membuat saya tenang.'],
        ['modus' => 'naratif', 'teks' => 'Saya senang membaca uraian panjang yang runtut.'],
        ['modus' => 'visual', 'teks' => 'Kalau melihat motif, saya langsung memperhatikan bentuk dan arahnya.'],
        ['modus' => 'simbolik', 'teks' => 'Saya suka memeriksa jawaban dengan cara menghitung ulang.'],
        ['modus' => 'naratif', 'teks' => 'Saya paham matematika lewat percakapan dan diskusi.'],
        ['modus' => 'visual', 'teks' => 'Saya paham lebih cepat dari video daripada dari teks.'],
        ['modus' => 'simbolik', 'teks' => 'Notasi seperti (x, y) → (x + a, y + b) terasa jelas bagi saya.'],
    ],

    // Minat konteks budaya: 2 pernyataan per artefak.
    'minat' => [
        ['artefak' => 'batik', 'teks' => 'Motif batik Tasikmalaya menarik bagi saya.'],
        ['artefak' => 'payung_geulis', 'teks' => 'Payung geulis dengan jari-jari dan hiasannya menarik bagi saya.'],
        ['artefak' => 'anyaman', 'teks' => 'Pola anyaman bambu Rajapolah menarik bagi saya.'],
        ['artefak' => 'batik', 'teks' => 'Saya ingin tahu cara motif batik disusun.'],
        ['artefak' => 'payung_geulis', 'teks' => 'Saya ingin mencoba merancang hiasan payung.'],
        ['artefak' => 'anyaman', 'teks' => 'Saya ingin membuat pola anyaman sendiri.'],
    ],

    'label_artefak' => [
        'batik' => 'Batik Tasikmalaya',
        'payung_geulis' => 'Payung Geulis',
        'anyaman' => 'Anyaman Rajapolah',
    ],

    'label_modus' => [
        'visual' => 'Visual-Manipulatif',
        'simbolik' => 'Simbolik-Analitis',
        'naratif' => 'Naratif-Kontekstual',
        'campuran' => 'Campuran',
    ],

    // Kategori kepraktisan dari rerata skor angket (skala 1–4); pasangan [ambang, label]
    // diperiksa dari yang tertinggi — bukan array berkunci float (lihat AikenCalculator).
    'kepraktisan' => [[3.25, 'sangat praktis'], [2.50, 'praktis'], [1.75, 'kurang praktis'], [0.0, 'tidak praktis']],

    'label_level' => [
        'L1' => 'Dasar',
        'L2' => 'Berkembang',
        'L3' => 'Mahir',
    ],
];
