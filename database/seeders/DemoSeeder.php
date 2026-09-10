<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\ContentVariant;
use App\Models\CulturalAsset;
use App\Models\LessonUnit;
use App\Models\Meeting;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh untuk PENGEMBANGAN LOKAL — tidak dipanggil DatabaseSeeder dan
 * tidak boleh dijalankan di server sekolah. Membuat satu sekolah, satu guru,
 * satu kelas berisi tiga siswa dengan PIN tetap, dan konten contoh Pertemuan 1
 * (empat varian formalisasi, kuis latihan, pemeriksaan penguasaan, refleksi).
 *
 *   php artisan db:seed --class=DemoSeeder
 *   guru : username guru1 / sandi password
 *   siswa: NIS 0056781234 / PIN 482913, 0056781235 / 736251, 0056781236 / 259147
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, ReadinessItemSeeder::class, MeetingSeeder::class, MotifSasaranSeeder::class, CtTestSeeder::class, RubricSeeder::class, InstrumenPenelitianSeeder::class]);

        $sekolah = School::query()->firstOrCreate(['npsn' => '20210001'], [
            'nama' => 'SMAN 1 Contoh Tasikmalaya', 'kabupaten_kota' => 'Kota Tasikmalaya',
        ]);

        $guru = User::query()->firstOrCreate(['username' => 'guru1'], [
            'nama' => 'Vepi Nurhasanah', 'email' => 'guru1@geulis.test', 'password' => 'password', 'school_id' => $sekolah->id,
        ]);
        $guru->berikanPeran(Peran::Guru);

        $kelas = Classroom::query()->firstOrCreate(['nama' => 'XI MIPA 3', 'school_id' => $sekolah->id], [
            'guru_id' => $guru->id, 'tahun_ajaran' => '2026/2027', 'kelompok_riset' => 'uji_terbatas',
        ]);

        $siswa = [['0056781234', 'Reza Pratama', 'L', '482913'], ['0056781235', 'Siti Aminah', 'P', '736251'], ['0056781236', 'Fikri Ramadhan', 'L', '259147']];
        foreach ($siswa as [$nis, $nama, $jk, $pin]) {
            $s = User::query()->firstOrCreate(['username' => $nis], [
                'nama' => $nama, 'jenis_kelamin' => $jk, 'password' => $pin, 'pin_kartu' => $pin,
                'school_id' => $sekolah->id, 'kode_anonim' => User::kodeAnonimBerikutnya('S'),
            ]);
            $s->berikanPeran(Peran::Siswa);
            $kelas->enrollments()->firstOrCreate(['user_id' => $s->id]);
        }

        // Gambar contoh disalin dari repo ke storage publik (storage/app/public tidak masuk Git).
        Storage::disk('public')->put('aset-budaya/contoh-anyaman.svg', file_get_contents(__DIR__.'/data/contoh-anyaman.svg'));

        $aset = CulturalAsset::query()->firstOrCreate(['nama_motif' => 'Kisi Anyaman Rajapolah (contoh)'], [
            'artefak' => 'anyaman', 'perajin_sumber' => 'Pak Dadang (contoh)', 'lokasi' => 'Rajapolah, Kab. Tasikmalaya',
            'tanggal_dokumentasi' => '2026-05-12', 'izin_diperoleh' => true, 'berkas' => 'aset-budaya/contoh-anyaman.svg',
            'catatan_matematis' => 'Satuan pengulangan bergeser dua arah: translasi oleh vektor (2,0) dan (0,2) satuan kisi.',
        ]);

        $p1 = Meeting::query()->where('urutan', 1)->firstOrFail();
        $p1->update(['terbit' => true]);
        $unit = fn (string $tipe): LessonUnit => $p1->lessonUnits()->where('tipe', $tipe)->firstOrFail();

        $varian = function (LessonUnit $u, string $level, string $modus, string $judul, string $badan, array $ekstra = []): void {
            ContentVariant::query()->updateOrCreate(
                ['lesson_unit_id' => $u->id, 'level' => $level, 'modus' => $modus],
                ['judul' => $judul, 'badan_konten' => $badan, 'status' => 'siap'] + $ekstra,
            );
        };

        $varian($unit('jangkar'), '*', '*', 'Kisi yang berulang', '<p>Perhatikan anyaman bambu ini. Kalau satu kotak kamu geser ke kanan dua satuan, apakah kamu mendapat kotak yang sama persis? Ke bawah? Berapa pergeseran terkecil yang membuat polanya "menutup" lagi?</p>', ['cultural_asset_id' => $aset->id]);

        $varian($unit('eksplorasi'), '*', 'visual', 'Geser satuan pengulangan', '<p>Geser vektor <b>v</b> dan lihat ke mana motif dasar berpindah. Cari dua vektor yang membuat pola menutup sempurna.</p>', [
            'konfigurasi_geogebra' => ['app' => 'geometry', 'terpandu' => true, 'perintah' => "A=(0,0)\nB=(2,0)\nC=(2,2)\nD=(0,2)\nmotif=Polygon(A,B,C,D)\nv=Vector((2,0))\nmotif'=Translate(motif, v)", 'alat' => ['0', '1', '40']],
        ]);
        $varian($unit('eksplorasi'), '*', '*', 'Menggeser motif dengan vektor', '<p>Tulis <code>Translate(motif, Vector((a,b)))</code> untuk beberapa nilai a dan b. Catat kapan bayangannya tepat menutupi kotak lain pada kisi.</p>', [
            'konfigurasi_geogebra' => ['app' => 'geometry', 'terpandu' => false, 'perintah' => "A=(0,0)\nB=(2,0)\nC=(2,2)\nD=(0,2)\nmotif=Polygon(A,B,C,D)"],
        ]);

        $f = $unit('formalisasi');
        $varian($f, 'L1', '*', 'Menggeser titik demi titik', '<p>Translasi memindahkan setiap titik <b>sejauh dan searah yang sama</b>. Kalau titik (1, 2) digeser 3 ke kanan dan 1 ke atas, hasilnya (1+3, 2+1) = (4, 3).</p><p>Coba: (0, 0) digeser 2 ke kanan, 2 ke atas → (…, …).</p>');
        $varian($f, 'L2', 'visual', 'Vektor sebagai anak panah', '<p>Setiap kotak anyaman berpindah mengikuti satu anak panah yang sama: vektor <b>v = (a, b)</b>. Semua titik motif berpindah sejauh anak panah itu.</p>');
        $varian($f, 'L2', '*', 'Aturan translasi', '<p>Translasi oleh vektor (a, b): <b>(x, y) ⟼ (x + a, y + b)</b>.</p><p>Pada anyaman Rajapolah, motif dasar berulang oleh dua vektor: (2, 0) dan (0, 2). Komposisi keduanya menghasilkan seluruh kisi.</p>');
        $varian($f, 'L3', '*', 'Komposisi translasi dan kisi', '<p>Komposisi dua translasi adalah translasi oleh jumlah vektornya: T<sub>u</sub> ∘ T<sub>v</sub> = T<sub>u+v</sub>. Kisi anyaman adalah himpunan {m·u + n·v : m, n ∈ ℤ}. Apakah urutan komposisi berpengaruh? Buktikan.</p>');

        Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('latihan')->id, 'tipe' => 'kuis'], [
            'judul' => 'Latihan translasi', 'konfigurasi' => ['butir' => [
                ['pertanyaan' => 'Bayangan titik (1, 2) oleh translasi (3, 1) adalah …', 'pilihan' => ['(4, 3)', '(3, 2)', '(−2, 1)', '(1, 5)'], 'kunci' => 0, 'label' => 'menjumlahkan komponen vektor'],
                ['pertanyaan' => 'Titik (5, −1) digeser oleh (−2, 4). Hasilnya …', 'pilihan' => ['(7, 3)', '(3, 3)', '(3, −5)', '(−10, −4)'], 'kunci' => 1, 'label' => 'tanda pada komponen negatif'],
                ['pertanyaan' => 'Vektor yang memindahkan (2, 3) ke (2, 7) adalah …', 'pilihan' => ['(0, 4)', '(4, 0)', '(4, 10)', '(0, −4)'], 'kunci' => 0, 'label' => 'menentukan vektor dari dua titik'],
            ]],
        ]);

        Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('pemeriksaan')->id, 'tipe' => 'kuis'], [
            'judul' => 'Pemeriksaan Penguasaan: Translasi', 'konfigurasi' => ['butir' => [
                ['pertanyaan' => 'Bayangan (−3, 2) oleh translasi (5, −6) adalah …', 'pilihan' => ['(2, −4)', '(−8, 8)', '(2, 4)', '(8, −4)'], 'kunci' => 0, 'label' => 'menjumlahkan komponen vektor'],
                ['pertanyaan' => 'Segitiga digeser oleh (1, 1) lalu (2, −3). Translasi tunggal yang setara …', 'pilihan' => ['(3, −2)', '(1, −4)', '(2, −3)', '(−1, 4)'], 'kunci' => 0, 'label' => 'komposisi translasi'],
                ['pertanyaan' => 'Motif dasar anyaman berulang oleh (2, 0). Kotak ke-4 dari motif dasar ada di …', 'pilihan' => ['(6, 0)', '(8, 0)', '(4, 0)', '(2, 4)'], 'kunci' => 0, 'label' => 'pengulangan translasi'],
                ['pertanyaan' => 'Jika (x, y) ⟼ (x − 2, y + 5) memetakan P ke (0, 3), maka P = …', 'pilihan' => ['(2, −2)', '(−2, 8)', '(2, 8)', '(−2, −2)'], 'kunci' => 0, 'label' => 'translasi balik'],
            ]],
        ]);

        Activity::query()->updateOrCreate(['lesson_unit_id' => $unit('refleksi')->id, 'tipe' => 'refleksi'], [
            'judul' => 'Refleksi Pertemuan 1', 'konfigurasi' => ['pertanyaan' => [
                'Bagian mana dari anyaman yang paling membantumu memahami translasi?',
                'Apa yang masih membingungkan tentang vektor perpindahan?',
            ]],
        ]);
    }
}
