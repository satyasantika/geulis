<?php

use App\Livewire\Angket\AngketPersepsi;
use App\Livewire\Peneliti\Analitik;
use App\Livewire\Siswa\Jalur;
use App\Models\Classroom;
use App\Models\Questionnaire;
use App\Models\QuestionnaireReflection;
use App\Models\User;
use App\Services\Research\PengeksporData;
use Database\Seeders\InstrumenPenelitianSeeder;
use Database\Seeders\InstrumenPersepsiSeeder;
use Database\Seeders\MeetingSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed([MeetingSeeder::class, InstrumenPenelitianSeeder::class, InstrumenPersepsiSeeder::class]);
    $this->peneliti = User::factory()->peneliti()->create();
    $this->eksperimen = Classroom::factory()->eksperimen()->create(['nama' => 'XI-3']);

    $this->buatSiswa = function (string $nama, ?string $kode = null): User {
        $s = User::factory()->siswa()->create(['nama' => $nama, 'kode_anonim' => $kode ?? User::kodeAnonimBerikutnya('S')]);
        $s->consent()->create(['setuju_data_penelitian' => true, 'disetujui_pada' => now()]);
        $this->eksperimen->enrollments()->create(['user_id' => $s->id]);
        $s->placement()->create(['skor_readiness' => 70, 'level_awal' => 'L2', 'modus' => 'visual', 'artefak_utama' => 'batik', 'penjelasan' => []]);

        return $s;
    };
});

it('appears on the student path as soon as it is active, regardless of meeting progress', function (): void {
    $siswa = ($this->buatSiswa)('Rani');

    Livewire::actingAs($siswa)->test(Jalur::class)->assertDontSee('Isi angket persepsi');

    Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->update(['aktif' => true]);

    // Tidak ada pertemuan yang ditandai selesai -- tautan tetap muncul karena tidak terikat progres.
    Livewire::actingAs($siswa)->test(Jalur::class)->assertSee('Isi angket persepsi');
});

it('blocks submission when statements are unanswered and stores partial answers', function (): void {
    $siswa = ($this->buatSiswa)('Dewi');
    Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->update(['aktif' => true]);
    $angket = Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->firstOrFail();

    Livewire::actingAs($siswa)->test(AngketPersepsi::class)
        ->set("jawaban.{$angket->items[0]->id}", 4)
        ->call('kirim')
        ->assertHasErrors(['kirim']);

    expect($siswa->questionnaireResponses()->count())->toBe(1)
        ->and(QuestionnaireReflection::query()->count())->toBe(0);
});

it('accepts a full 30-item submission with optional qualitative notes', function (): void {
    $siswa = ($this->buatSiswa)('Fajar');
    Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->update(['aktif' => true]);
    $angket = Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->firstOrFail();

    expect($angket->items)->toHaveCount(30);

    $komponen = Livewire::actingAs($siswa)->test(AngketPersepsi::class);
    foreach ($angket->items as $i) {
        $komponen->set("jawaban.{$i->id}", 5);
    }
    $komponen->set('catatan.disukai', 'Motif Builder')
        ->set('catatan.saran', 'Lebih banyak contoh')
        // 'diperbaiki' sengaja dibiarkan kosong -- field kualitatif opsional.
        ->call('kirim')->assertHasNoErrors()->assertSee('sudah terkirim');

    expect($siswa->questionnaireResponses()->count())->toBe(30);

    $catatan = QuestionnaireReflection::query()->where('user_id', $siswa->id)->get()->keyBy('kode');
    expect($catatan)->toHaveCount(2)
        ->and($catatan['disukai']->jawaban)->toBe('Motif Builder')
        ->and($catatan['saran']->jawaban)->toBe('Lebih banyak contoh');
});

it('keeps the practicality rekap correct in Analitik when both questionnaires are active at once', function (): void {
    $siswa = ($this->buatSiswa)('Guess Guard');
    Questionnaire::query()->where('sasaran', 'siswa')->update(['aktif' => true]); // kepraktisan DAN persepsi sekaligus

    $kepraktisan = Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'kepraktisan')->firstOrFail();
    foreach ($kepraktisan->items as $i) {
        $siswa->questionnaireResponses()->create(['questionnaire_item_id' => $i->id, 'skor' => $i->butir_negatif ? 1 : 4]);
    }

    Livewire::actingAs($this->peneliti)->test(Analitik::class)->assertSee('sangat praktis');
});

it('exports an anonymised angket_persepsi sheet', function (): void {
    $siswa = ($this->buatSiswa)('Reza', kode: 'S-901');
    Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->update(['aktif' => true]);
    $angket = Questionnaire::query()->where('sasaran', 'siswa')->where('jenis', 'persepsi')->firstOrFail();

    $komponen = Livewire::actingAs($siswa)->test(AngketPersepsi::class);
    foreach ($angket->items as $i) {
        $komponen->set("jawaban.{$i->id}", 4);
    }
    $komponen->set('catatan.disukai', 'Simulasi interaktif')->call('kirim')->assertHasNoErrors();

    $lembar = app(PengeksporData::class)->lembar();

    expect($lembar)->toHaveKey('angket_persepsi');
    $baris = collect($lembar['angket_persepsi']);
    $header = $baris->first();
    expect($header)->toContain('disukai', 'diperbaiki', 'saran');

    $barisSiswa = $baris->skip(1)->firstWhere(0, 'S-901');
    expect($barisSiswa)->not->toBeNull()
        ->and($barisSiswa)->toContain('Simulasi interaktif');

    $teks = json_encode($lembar);
    expect($teks)->toContain('S-901')->not->toContain('Reza');
});
