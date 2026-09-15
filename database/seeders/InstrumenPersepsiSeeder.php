<?php

namespace Database\Seeders;

use App\Models\Questionnaire;
use App\Models\QuestionnaireItem;
use Illuminate\Database\Seeder;

/**
 * Angket Persepsi Peserta Didik (30 butir, 9 aspek, skala 1-5) -- instrumen
 * berbeda dari Angket Respons Siswa (kepraktisan, 12 butir) yang diseed oleh
 * InstrumenPenelitianSeeder. Dipisah sengaja: ModulPenelitianTest men-seed
 * InstrumenPenelitianSeeder langsung tanpa instrumen ini, jadi test lama
 * tidak pernah melihat dua "siswa"-questionnaire aktif sekaligus.
 *
 * Sumber: draf instrumen peneliti, belum divalidasi ahli -- aktif=false.
 * Seluruh butir favorable (tidak ada butir_negatif), sesuai catatan sumber.
 */
class InstrumenPersepsiSeeder extends Seeder
{
    public function run(): void
    {
        $angket = Questionnaire::query()->updateOrCreate(
            ['sasaran' => 'siswa', 'jenis' => 'persepsi'],
            ['nama' => 'Angket Respon/Persepsi Peserta Didik terhadap Penggunaan LMS GEULIS', 'skala_maks' => 5, 'aktif' => false],
        );

        $butir = [
            ['Kebermanfaatan', 'LMS GEULIS membantu saya memahami konsep transformasi geometri.'],
            ['Kebermanfaatan', 'LMS GEULIS membantu saya belajar translasi, refleksi, rotasi, dan dilatasi dengan lebih mudah.'],
            ['Kebermanfaatan', 'Aktivitas dalam GEULIS membantu saya menghubungkan konsep transformasi geometri dengan situasi kehidupan sehari-hari.'],
            ['Kebermanfaatan', 'LMS GEULIS membuat pembelajaran transformasi geometri menjadi lebih bermakna bagi saya.'],

            ['Kemudahan Penggunaan', 'LMS GEULIS mudah saya akses menggunakan perangkat yang saya gunakan.'],
            ['Kemudahan Penggunaan', 'Menu dan navigasi GEULIS mudah saya pahami.'],
            ['Kemudahan Penggunaan', 'Instruksi pada aktivitas pembelajaran GEULIS mudah saya ikuti.'],
            ['Kemudahan Penggunaan', 'Saya dapat menggunakan fitur-fitur GEULIS tanpa banyak mengalami kesulitan.'],

            ['Efisiensi', 'LMS GEULIS membantu saya menyelesaikan kegiatan belajar secara lebih efisien.'],
            ['Efisiensi', 'LMS GEULIS membantu saya mengatur urutan kegiatan belajar yang harus saya lakukan.'],
            ['Efisiensi', 'LMS GEULIS memberi saya kesempatan untuk mempelajari kembali materi atau aktivitas ketika saya membutuhkannya.'],

            ['Adaptif/Diferensiasi', 'LMS GEULIS memberi pengalaman belajar yang dapat menyesuaikan dengan kebutuhan belajar saya.'],
            ['Adaptif/Diferensiasi', 'LMS GEULIS membantu saya belajar sesuai dengan kemampuan saya.'],
            ['Adaptif/Diferensiasi', 'Saya dapat belajar dengan cara dan kecepatan yang sesuai dengan kondisi belajar saya melalui GEULIS.'],
            ['Adaptif/Diferensiasi', 'LMS GEULIS membantu saya belajar secara lebih mandiri ketika saya mengalami kesulitan.'],

            ['Etnomatematika Priangan Timur', 'Konteks budaya Priangan Timur dalam GEULIS membuat materi matematika lebih dekat dengan kehidupan saya.'],
            ['Etnomatematika Priangan Timur', 'Penggunaan motif atau produk budaya lokal dalam GEULIS membuat pembelajaran transformasi geometri lebih menarik.'],
            ['Etnomatematika Priangan Timur', 'LMS GEULIS membantu saya melihat bahwa konsep transformasi geometri dapat ditemukan dalam budaya dan lingkungan sekitar.'],

            ['CT - Dekomposisi', 'Aktivitas dalam GEULIS membantu saya memecah masalah transformasi geometri yang kompleks menjadi bagian-bagian yang lebih sederhana.'],
            ['CT - Pengenalan Pola', 'Aktivitas dalam GEULIS membantu saya menemukan pola atau kesamaan pada berbagai masalah transformasi geometri.'],
            ['CT - Berpikir Algoritma', 'Aktivitas dalam GEULIS membantu saya menyusun langkah-langkah yang runtut untuk menyelesaikan masalah transformasi geometri.'],
            ['CT - Generalisasi Pola & Abstraksi', 'Aktivitas dalam GEULIS membantu saya menemukan informasi penting dan menggunakan pola yang diperoleh untuk menyelesaikan masalah lain yang serupa.'],

            ['Interaktivitas & Engagement', 'Aktivitas dalam GEULIS membuat saya lebih aktif selama pembelajaran matematika.'],
            ['Interaktivitas & Engagement', 'LMS GEULIS membuat saya tertarik untuk mengeksplorasi materi dan aktivitas transformasi geometri.'],
            ['Interaktivitas & Engagement', 'Saya lebih termotivasi untuk menyelesaikan aktivitas pembelajaran ketika menggunakan GEULIS.'],

            ['Visualisasi & Representasi', 'Visualisasi pada GEULIS membantu saya memahami perubahan posisi objek akibat transformasi geometri.'],
            ['Visualisasi & Representasi', 'Gambar, bidang koordinat, atau representasi visual dalam GEULIS membantu saya memahami konsep transformasi geometri.'],
            ['Visualisasi & Representasi', 'LMS GEULIS membantu saya menghubungkan representasi visual dengan aturan atau perhitungan transformasi geometri.'],

            ['Kepuasan & Keterterimaan', 'Saya merasa nyaman dan puas belajar transformasi geometri menggunakan GEULIS.'],
            ['Kepuasan & Keterterimaan', 'Saya bersedia menggunakan GEULIS kembali pada pembelajaran matematika berikutnya.'],
        ];

        foreach ($butir as $i => [$aspek, $pernyataan]) {
            QuestionnaireItem::query()->updateOrCreate(
                ['questionnaire_id' => $angket->id, 'urutan' => $i + 1],
                ['aspek' => $aspek, 'pernyataan' => $pernyataan, 'butir_negatif' => false],
            );
        }
    }
}
