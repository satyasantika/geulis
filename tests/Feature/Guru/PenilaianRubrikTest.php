<?php

use App\Livewire\Guru\PenilaianRubrik;
use App\Livewire\Siswa\Kemajuan;
use App\Livewire\Siswa\Produk;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\CtTest;
use App\Models\Meeting;
use App\Models\Product;
use App\Models\Rubric;
use App\Models\RubricScore;
use App\Models\User;
use App\Services\Asesmen\PencatatTesCt;
use App\Services\Differentiation\AdaptationRecorder;
use Database\Seeders\MeetingSeeder;
use Database\Seeders\RubricSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Storage::fake('local');
    $this->seed([MeetingSeeder::class, RubricSeeder::class]);
    Meeting::query()->update(['terbit' => true]);
    $this->p1 = Meeting::query()->where('urutan', 1)->firstOrFail();

    $this->kelas = Classroom::factory()->create();
    $this->siswa = User::factory()->siswa()->create(['nama' => 'Siti']);
    $this->siswa->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
    $this->kelas->enrollments()->create(['user_id' => $this->siswa->id]);
    $this->siswa->placement()->create(['skor_readiness' => 72, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);
});

describe('S-10 unggah produk', function (): void {
    it('shows the rubric before submitting and stores the file privately', function (): void {
        Livewire::actingAs($this->siswa)->test(Produk::class, ['meeting' => $this->p1])
            ->assertSee('Rubrik penilaian (baca sebelum mengerjakan)')
            ->assertSee('Ketepatan matematis')
            ->set('bentuk', 'poster')
            ->set('berkas', UploadedFile::fake()->image('poster.png', 800, 600))
            ->set('deskripsi', 'Poster memetakan motif sawoan ke refleksi terhadap sumbu-y dengan algoritma dua langkah.')
            ->call('kirim')
            ->assertHasNoErrors();

        $produk = Product::query()->firstOrFail();

        expect($produk->bentuk)->toBe('poster')
            ->and($produk->dikirim_pada)->not->toBeNull()
            ->and(Storage::disk('local')->exists($produk->berkas))->toBeTrue()
            ->and($produk->berkas)->toStartWith('produk-siswa/'.$this->siswa->id.'/');
    });

    it('requires a form, a description, and a file or link', function (): void {
        Livewire::actingAs($this->siswa)->test(Produk::class, ['meeting' => $this->p1])
            ->call('kirim')
            ->assertHasErrors(['bentuk', 'deskripsi'])
            ->set('bentuk', 'video')
            ->set('deskripsi', 'Video menjelaskan rotasi 45 derajat pada payung geulis delapan jari-jari.')
            ->call('kirim')
            ->assertHasErrors(['berkas'])
            ->set('tautan', 'https://youtu.be/contoh')
            ->call('kirim')
            ->assertHasNoErrors();

        expect(Product::query()->first()->tautan)->toBe('https://youtu.be/contoh');
    });

    it('rejects a file over 2 MB', function (): void {
        Livewire::actingAs($this->siswa)->test(Produk::class, ['meeting' => $this->p1])
            ->set('bentuk', 'poster')
            ->set('berkas', UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf'))
            ->set('deskripsi', 'Deskripsi cukup panjang untuk lolos validasi minimum dua puluh karakter.')
            ->call('kirim')
            ->assertHasErrors(['berkas']);
    });

    it('serves the file only to the owner, their teacher, or research staff', function (): void {
        $produk = Product::query()->create(['user_id' => $this->siswa->id, 'meeting_id' => $this->p1->id, 'bentuk' => 'poster', 'berkas' => 'produk-siswa/'.$this->siswa->id.'/a.png', 'deskripsi' => 'x', 'dikirim_pada' => now()]);
        Storage::disk('local')->put($produk->berkas, 'PNG');

        actingAs($this->siswa)->get(route('produk.berkas', $produk))->assertOk();
        actingAs($this->kelas->guru)->get(route('produk.berkas', $produk))->assertOk();
        actingAs(User::factory()->peneliti()->create())->get(route('produk.berkas', $produk))->assertOk();
        actingAs(User::factory()->guru()->create())->get(route('produk.berkas', $produk))->assertForbidden();
        actingAs(User::factory()->siswa()->create())->get(route('produk.berkas', $produk))->assertForbidden();
    });
});

describe('G-03 penilaian rubrik', function (): void {
    it('grades all four criteria, computes the weighted score, and shows it to the student', function (): void {
        $produk = Product::query()->create(['user_id' => $this->siswa->id, 'meeting_id' => $this->p1->id, 'bentuk' => 'laporan_algoritmik', 'tautan' => 'https://x.y/z', 'deskripsi' => 'Laporan.', 'dikirim_pada' => now()]);
        $kriteria = Rubric::query()->where('untuk', 'produk_akhir')->firstOrFail()->criteria;

        $komponen = Livewire::actingAs($this->kelas->guru)->test(PenilaianRubrik::class, ['meeting' => $this->p1])
            ->assertSee('Siti')
            ->call('simpan', $produk->id)
            ->assertHasErrors(["tingkat.{$produk->id}"]);

        foreach ($kriteria as $i => $k) {
            $komponen->set("tingkat.{$produk->id}.{$k->id}", [4, 3, 2, 3][$i]);
        }
        $komponen->set("umpanBalik.{$produk->id}.{$kriteria[1]->id}", 'Perulangan belum dipakai.')
            ->call('simpan', $produk->id)
            ->assertHasNoErrors()
            ->assertSee('75/100');

        expect(RubricScore::query()->where('product_id', $produk->id)->count())->toBe(4)
            ->and(RubricScore::query()->where('rubric_criteria_id', $kriteria[1]->id)->first()->umpan_balik)->toBe('Perulangan belum dipakai.');

        Livewire::actingAs($this->siswa)->test(Kemajuan::class)
            ->assertSee('Laporan Algoritmik')
            ->assertSee('75')
            ->assertSee('Perulangan belum dipakai.');
    });

    it('hides products of students from other classes', function (): void {
        $lain = User::factory()->siswa()->create(['nama' => 'Orang Lain']);
        Product::query()->create(['user_id' => $lain->id, 'meeting_id' => $this->p1->id, 'bentuk' => 'poster', 'tautan' => 'https://x.y', 'deskripsi' => 'Rahasia kelas lain.', 'dikirim_pada' => now()]);

        Livewire::actingAs($this->kelas->guru)->test(PenilaianRubrik::class, ['meeting' => $this->p1])->assertDontSee('Orang Lain');
    });
});

describe('S-09 kemajuanku', function (): void {
    it('renders the CT bars, the mastery chart, and the motif gallery', function (): void {
        $tes = CtTest::factory()->denganButir()->create();
        app(PencatatTesCt::class)->kumpulkan($this->siswa, $tes, [$tes->items[0]->id => 'b']);
        $unit = $this->p1->lessonUnits->firstWhere('tipe', 'pemeriksaan');
        app(AdaptationRecorder::class)->evaluasiDanRekam($this->siswa, $unit, 0.9);
        $aktivitas = Activity::factory()->for($unit)->create(['tipe' => 'motif_builder', 'konfigurasi' => ['motif_dasar' => [[0, 0], [1, 0], [1, 1]], 'sasaran' => [['op' => 'MOTIF_DASAR']]]]);
        $this->siswa->motifSubmissions()->create(['activity_id' => $aktivitas->id, 'percobaan_ke' => 1, 'urutan_perintah' => [['op' => 'MOTIF_DASAR']], 'skor_kemiripan' => 94, 'langkah_siswa' => 1, 'langkah_minimum' => 1, 'efisiensi' => 100, 'lolos' => true, 'cuplikan_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="5" height="5"/></svg>']);

        Livewire::actingAs($this->siswa)->test(Kemajuan::class)
            ->assertSee('Dekomposisi')
            ->assertSee('uraian masih dinilai guru')
            ->assertSee('<polyline', escape: false)
            ->assertSee('94% ✓')
            ->assertSee('<rect width="5"', escape: false);
    });
});
