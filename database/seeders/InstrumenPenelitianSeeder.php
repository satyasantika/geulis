<?php

namespace Database\Seeders;

use App\Models\ObservationItem;
use App\Models\Questionnaire;
use App\Models\QuestionnaireItem;
use App\Models\ValidationInstrument;
use App\Models\ValidationItem;
use Illuminate\Database\Seeder;

/**
 * Instrumen penelitian: lembar validasi ahli (4 aspek × 7 = 28 butir, skala
 * 1–5), angket respons siswa (12) dan guru (10, skala 1–4, sebagian butir
 * negatif), dan lembar observasi keterlaksanaan (10 butir, skala 1–4).
 * Butir dapat direvisi tim; riwayat Git menjadi jejak revisinya.
 */
class InstrumenPenelitianSeeder extends Seeder
{
    public function run(): void
    {
        $instrumen = ValidationInstrument::query()->updateOrCreate(['nama' => 'Lembar Validasi Ahli GEULIS'], ['sasaran_validator' => 'ahli_materi', 'skala_maks' => 5]);

        $aspek = [
            'Kelayakan materi' => [
                'Materi transformasi geometri sesuai capaian pembelajaran kelas XI.',
                'Konsep translasi, refleksi, rotasi, dilatasi, dan komposisi disajikan benar secara matematis.',
                'Urutan lima pertemuan logis dan membangun satu sama lain.',
                'Contoh dan latihan mencerminkan tingkat kesulitan yang sesuai.',
                'Butir pemeriksaan penguasaan mengukur indikator yang dimaksud.',
                'Butir tes CT mengukur dekomposisi, pengenalan pola, abstraksi, dan algoritma sesuai definisi operasional.',
                'Bahasa dalam materi jelas dan tidak ambigu bagi siswa SMA.',
            ],
            'Desain pembelajaran berdiferensiasi' => [
                'Penempatan awal (tes kesiapan, profil belajar, minat) mendasari diferensiasi konten, proses, dan produk.',
                'Penjenjangan L1–L2–L3 pada tiap unit runtut dan beralasan.',
                'Tiga modus (visual, simbolik, naratif) menyajikan konsep yang sama dengan representasi berbeda.',
                'Aturan adaptasi (promosi, penguatan, remedial, eskalasi) masuk akal secara pedagogis.',
                'Nilai α = 0,4 pada pembaruan penguasaan sudah wajar.',
                'Ambang promosi 0,80 dan remedial 0,50 sudah wajar.',
                'Umpan balik kepada siswa tidak menghakimi dan menunjukkan jalan keluar.',
            ],
            'Integrasi etnomatematika' => [
                'Artefak (batik, payung geulis, anyaman) tepat merepresentasikan konsep transformasi yang dimaksud.',
                'Klaim matematis pada artefak benar-benar ada, bukan diada-adakan.',
                'Atribusi perajin/sumber ditampilkan dan izin dokumentasi jelas.',
                'Konteks budaya membantu pemahaman, bukan sekadar hiasan.',
                'Motif Builder menghubungkan artefak dengan perintah transformasi secara bermakna.',
                'Siswa tetap menemui ketiga artefak, tidak dikurung pada satu budaya.',
                'Pemilihan artefak menghormati komunitas asalnya.',
            ],
            'Kelayakan sistem sebagai media' => [
                'Antarmuka mudah dipakai pada telepon genggam (lebar 360 px).',
                'Alur masuk NIS + PIN praktis untuk siswa tanpa surel.',
                'Navigasi antarpertemuan dan antarbagian jelas.',
                'Motif Builder dapat digunakan dengan ibu jari tanpa pelatihan panjang.',
                'GeoGebra termuat dan responsif tanpa akses internet luar.',
                'Papan kelas guru menjawab "siapa yang perlu didatangi hari ini".',
                'Sistem menjaga privasi siswa (data minimal, kode anonim pada ekspor).',
            ],
        ];

        $urutan = 0;
        foreach ($aspek as $namaAspek => $butir) {
            foreach ($butir as $pernyataan) {
                ValidationItem::query()->updateOrCreate(
                    ['validation_instrument_id' => $instrumen->id, 'urutan' => ++$urutan],
                    ['aspek' => $namaAspek, 'pernyataan' => $pernyataan],
                );
            }
        }

        $angketSiswa = Questionnaire::query()->updateOrCreate(['sasaran' => 'siswa'], ['nama' => 'Angket Respons Siswa', 'skala_maks' => 4, 'aktif' => false]);
        $butirSiswa = [
            ['Kemudahan', 'Saya mudah masuk ke GEULIS dengan NIS dan PIN.', false],
            ['Kemudahan', 'Saya bisa memakai GEULIS dari HP tanpa kesulitan.', false],
            ['Kemudahan', 'Saya sering bingung harus membuka bagian mana.', true],
            ['Kemanfaatan', 'Contoh batik, payung, dan anyaman membantu saya memahami transformasi.', false],
            ['Kemanfaatan', 'Motif Builder membuat saya berpikir langkah demi langkah.', false],
            ['Kemanfaatan', 'Materi yang saya dapat terasa terlalu mudah atau terlalu sulit.', true],
            ['Kemanfaatan', 'Umpan balik setelah pemeriksaan membantu saya tahu apa yang harus diperbaiki.', false],
            ['Kemenarikan', 'Saya ingin mencoba lagi ketika motif saya belum mirip sasaran.', false],
            ['Kemenarikan', 'Belajar dengan GEULIS membosankan.', true],
            ['Kemenarikan', 'Saya bangga dengan motif yang saya rancang.', false],
            ['Efisiensi', 'Waktu belajar terasa cukup untuk tiap pertemuan.', false],
            ['Efisiensi', 'GeoGebra dan Motif Builder terbuka cukup cepat di HP saya.', false],
        ];
        foreach ($butirSiswa as $i => [$aspek, $pernyataan, $negatif]) {
            QuestionnaireItem::query()->updateOrCreate(['questionnaire_id' => $angketSiswa->id, 'urutan' => $i + 1], ['aspek' => $aspek, 'pernyataan' => $pernyataan, 'butir_negatif' => $negatif]);
        }

        $angketGuru = Questionnaire::query()->updateOrCreate(['sasaran' => 'guru'], ['nama' => 'Angket Respons Guru', 'skala_maks' => 4, 'aktif' => false]);
        $butirGuru = [
            ['Kemudahan', 'Membuat kelas dan mengimpor siswa dari CSV mudah dilakukan.', false],
            ['Kemudahan', 'Kartu PIN memudahkan siswa masuk pada hari pertama.', false],
            ['Kemudahan', 'Saya kesulitan memahami papan kelas.', true],
            ['Kemanfaatan', 'Daftar "perlu pendampingan" membantu saya memutuskan siapa yang didatangi.', false],
            ['Kemanfaatan', 'Alasan sistem menaikkan/menurunkan level siswa dapat saya pahami.', false],
            ['Kemanfaatan', 'Fitur override membuat saya tetap memegang kendali pedagogis.', false],
            ['Kemanfaatan', 'Rubrik empat tingkat memudahkan penilaian produk secara konsisten.', false],
            ['Efisiensi', 'Beban administrasi saya berkurang dibanding pembelajaran biasa.', false],
            ['Efisiensi', 'Menilai uraian tes CT memakan waktu terlalu lama.', true],
            ['Keberlanjutan', 'Saya bersedia memakai GEULIS pada semester berikutnya.', false],
        ];
        foreach ($butirGuru as $i => [$aspek, $pernyataan, $negatif]) {
            QuestionnaireItem::query()->updateOrCreate(['questionnaire_id' => $angketGuru->id, 'urutan' => $i + 1], ['aspek' => $aspek, 'pernyataan' => $pernyataan, 'butir_negatif' => $negatif]);
        }

        $observasi = [
            ['Pembukaan', 'Guru membuka pertemuan dengan jangkar budaya (foto/video artefak) dan pertanyaan pemantik.'],
            ['Pembukaan', 'Siswa masuk ke sistem dan membuka pertemuan yang dimaksud dalam ≤ 5 menit.'],
            ['Inti', 'Siswa mengerjakan eksplorasi GeoGebra sesuai varian levelnya.'],
            ['Inti', 'Siswa berlevel berbeda tampak mengerjakan konten yang berbeda.'],
            ['Inti', 'Guru mendatangi siswa yang muncul di daftar "perlu pendampingan".'],
            ['Inti', 'Siswa menyusun perintah di Motif Builder dan menjalankannya.'],
            ['Inti', 'Siswa berdiskusi tentang transformasi pada artefak, bukan hanya mengeklik.'],
            ['Penutup', 'Siswa mengisi refleksi dua pertanyaan.'],
            ['Penutup', 'Guru merangkum keterkaitan artefak dan konsep transformasi.'],
            ['Teknis', 'Sistem berjalan tanpa gangguan teknis yang menghentikan pembelajaran.'],
        ];
        foreach ($observasi as $i => [$aspek, $pernyataan]) {
            ObservationItem::query()->updateOrCreate(['urutan' => $i + 1], ['aspek' => $aspek, 'pernyataan' => $pernyataan]);
        }
    }
}
