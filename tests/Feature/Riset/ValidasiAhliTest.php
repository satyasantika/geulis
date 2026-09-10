<?php

use App\Livewire\Peneliti\Validasi;
use App\Livewire\Validasi\LembarValidasi;
use App\Models\AikenResult;
use App\Models\ExpertValidation;
use App\Models\User;
use App\Models\ValidationInstrument;
use Database\Seeders\InstrumenPenelitianSeeder;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->seed(InstrumenPenelitianSeeder::class);
    $this->instrumen = ValidationInstrument::query()->firstOrFail();
    $this->peneliti = User::factory()->peneliti()->create();
});

function undangValidator(ValidationInstrument $instrumen, string $nama): ExpertValidation
{
    $v = User::factory()->validator()->create(['nama' => $nama]);

    return ExpertValidation::query()->create(['validator_id' => $v->id, 'validation_instrument_id' => $instrumen->id]);
}

it('seeds 28 items in four aspects on a 1–5 scale', function (): void {
    expect($this->instrumen->items()->count())->toBe(28)
        ->and($this->instrumen->items()->distinct('aspek')->count('aspek'))->toBe(4)
        ->and($this->instrumen->skala_maks)->toBe(5);
});

it('lets a researcher create an invitation link and shows it', function (): void {
    Livewire::actingAs($this->peneliti)->test(Validasi::class)
        ->set('namaValidator', 'Prof. Dr. Ahli Materi')
        ->set('emailValidator', 'ahli@kampus.ac.id')
        ->call('undang')
        ->assertHasNoErrors()
        ->assertSee('Prof. Dr. Ahli Materi')
        ->assertSee('/validasi/');

    $undangan = ExpertValidation::query()->firstOrFail();

    expect($undangan->validator->punyaPeran('validator'))->toBeTrue()
        ->and(strlen($undangan->token))->toBe(64)
        ->and($undangan->status)->toBe('dikirim');
});

it('opens only through a valid signed link and without an account', function (): void {
    $undangan = undangValidator($this->instrumen, 'Validator A');

    get(route('validasi.form', ['token' => $undangan->token]))->assertForbidden();          // tanpa tanda tangan
    get(URL::signedRoute('validasi.form', ['token' => 'salah']))->assertNotFound();

    get(Validasi::tautan($undangan))->assertOk()->assertSee('Lembar Validasi Ahli GEULIS')->assertSee('Validator A');

    expect($undangan->fresh()->status)->toBe('dibuka');
});

it('saves ratings item by item, resumes them, and only submits when complete', function (): void {
    $undangan = undangValidator($this->instrumen, 'Validator B');
    $items = $this->instrumen->items;

    $komponen = Livewire::test(LembarValidasi::class, ['token' => $undangan->token])
        ->set("skor.{$items[0]->id}", 5)
        ->set("saran.{$items[0]->id}", 'L1 masih melompat ke matriks.')
        ->set("skor.{$items[0]->id}", 4)
        ->call('kirim')
        ->assertHasErrors(['kirim']);

    expect($undangan->ratings()->count())->toBe(1)
        ->and($undangan->ratings()->first()->skor)->toBe(4)
        ->and($undangan->ratings()->first()->saran)->toBe('L1 masih melompat ke matriks.')
        ->and($undangan->fresh()->status)->toBe('dibuka');

    Livewire::test(LembarValidasi::class, ['token' => $undangan->token])->assertSet("skor.{$items[0]->id}", 4);

    foreach ($items as $item) {
        $komponen->set("skor.{$item->id}", 5);
    }
    $komponen->call('aspekBerikutnya')->call('kirim')->assertHasNoErrors()->assertSee('sudah terkirim');

    expect($undangan->fresh()->status)->toBe('selesai')
        ->and($undangan->fresh()->selesai_pada)->not->toBeNull()
        ->and($undangan->ratings()->count())->toBe(28);
});

it("computes Aiken's V per item, aspect, and overall matching a hand calculation", function (): void {
    $items = $this->instrumen->items;
    $skorPenilai = [[5, 4, 5], [4, 3, 3], [2, 2, 1]]; // butir 1: V=(4+3+4)/12=0,917; butir 2: (3+2+2)/12=0,583; butir 3: (1+1+0)/12=0,167

    foreach (['A', 'B', 'C'] as $i => $nama) {
        $u = undangValidator($this->instrumen, "Validator {$nama}");
        foreach ($items as $j => $item) {
            $u->ratings()->create(['validation_item_id' => $item->id, 'skor' => $skorPenilai[$j][$i] ?? 5, 'saran' => $j === 2 && $i === 0 ? 'Butir 3 mengukur hitungan, bukan abstraksi.' : null]);
        }
        $u->update(['status' => 'selesai', 'selesai_pada' => now()]);
    }

    Livewire::actingAs($this->peneliti)->test(Validasi::class)
        ->assertSee('0,92')
        ->assertSee('0,58')
        ->assertSee('0,17')
        ->assertSee('3 penilai')
        ->assertSee('Butir 3 mengukur hitungan');

    $hasil = AikenResult::query()->whereIn('validation_item_id', $items->take(3)->pluck('id'))->orderBy('validation_item_id')->get();

    expect($hasil->pluck('nilai_v')->all())->toBe([0.917, 0.583, 0.167])
        ->and($hasil->pluck('kategori')->all())->toBe(['tinggi', 'sedang', 'rendah'])
        ->and($hasil->first()->n_penilai)->toBe(3);

    $csv = actingAs($this->peneliti)->get(route('riset.saran'))->assertOk()->streamedContent();

    expect($csv)->toContain('Butir 3 mengukur hitungan')->toContain('belum');
});

it('keeps the researcher panel away from teachers', function (): void {
    actingAs(User::factory()->guru()->create())->get(route('riset.validasi'))->assertForbidden();
    actingAs($this->peneliti)->get(route('riset.validasi'))->assertOk();
});
