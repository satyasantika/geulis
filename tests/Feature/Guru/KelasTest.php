<?php

use App\Enums\Peran;
use App\Livewire\Guru\Beranda;
use App\Livewire\Guru\DetailKelas;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

describe('G-00 beranda guru', function (): void {
    it('lets a teacher create a class with an auto-generated join code', function (): void {
        $guru = User::factory()->guru()->create();
        $sekolah = School::factory()->create();

        Livewire::actingAs($guru)
            ->test(Beranda::class)
            ->set('nama', 'XI MIPA 3')
            ->set('tahun_ajaran', '2026/2027')
            ->set('school_id', $sekolah->id)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSee('XI MIPA 3');

        $kelas = Classroom::query()->where('nama', 'XI MIPA 3')->firstOrFail();

        expect($kelas->guru_id)->toBe($guru->id)
            ->and($kelas->kode_gabung)->toMatch('/^[A-Z0-9]{6}$/');
    });

    it('rejects a malformed school year', function (): void {
        Livewire::actingAs(User::factory()->guru()->create())
            ->test(Beranda::class)
            ->set('nama', 'XI MIPA 3')
            ->set('tahun_ajaran', '2026')
            ->set('school_id', School::factory()->create()->id)
            ->call('simpan')
            ->assertHasErrors(['tahun_ajaran']);
    });
});

describe('impor siswa', function (): void {
    it('imports 30 students from CSV with a student role, PIN, and anonymous code', function (): void {
        $kelas = Classroom::factory()->create();
        $baris = collect(range(1, 30))
            ->map(fn (int $i) => sprintf('Siswa %02d,%010d,%s', $i, $i, $i % 2 ? 'L' : 'P'))
            ->implode("\n");

        Livewire::actingAs($kelas->guru)
            ->test(DetailKelas::class, ['classroom' => $kelas])
            ->set('berkas', UploadedFile::fake()->createWithContent('siswa.csv', "nama,nis,jenis_kelamin\n{$baris}\n"))
            ->call('impor')
            ->assertHasNoErrors()
            ->assertSee('30 siswa baru dibuat');

        expect($kelas->siswa()->count())->toBe(30);

        $pertama = User::query()->where('username', '0000000001')->firstOrFail();

        expect($pertama->punyaPeran(Peran::Siswa))->toBeTrue()
            ->and($pertama->kode_anonim)->toBe('S-001')
            ->and($pertama->pin_kartu)->toMatch('/^\d{6}$/')
            ->and(Hash::check($pertama->pin_kartu, $pertama->password))->toBeTrue()
            ->and($pertama->school_id)->toBe($kelas->school_id)
            ->and(User::query()->where('username', '0000000030')->value('kode_anonim'))->toBe('S-030');
    });

    it('re-enrolls an existing student instead of duplicating the account', function (): void {
        $kelas = Classroom::factory()->create();
        $lama = User::factory()->siswa()->create(['username' => '0056781234', 'kode_anonim' => 'S-007']);

        Livewire::actingAs($kelas->guru)
            ->test(DetailKelas::class, ['classroom' => $kelas])
            ->set('berkas', UploadedFile::fake()->createWithContent('siswa.csv', "nama,nis,jenis_kelamin\nReza,0056781234,L\nBaru,0056781299,P\n"))
            ->call('impor')
            ->assertSee('1 siswa baru dibuat, 1 sudah terdaftar sebelumnya');

        expect(User::query()->where('username', '0056781234')->count())->toBe(1)
            ->and($kelas->siswa()->pluck('users.id'))->toContain($lama->id)
            ->and(User::query()->where('username', '0056781299')->value('kode_anonim'))->toBe('S-008');
    });

    it('refuses a NIS that belongs to a non-student account', function (): void {
        $kelas = Classroom::factory()->create();
        User::factory()->guru()->create(['username' => 'guru-77']);

        Livewire::actingAs($kelas->guru)
            ->test(DetailKelas::class, ['classroom' => $kelas])
            ->set('namaBaru', 'Penyusup')
            ->set('nisBaru', '77')
            ->call('tambahSatu');

        expect(User::query()->where('username', '77')->exists())->toBeFalse();
    });

    it('adds a single student by hand', function (): void {
        $kelas = Classroom::factory()->create();

        Livewire::actingAs($kelas->guru)
            ->test(DetailKelas::class, ['classroom' => $kelas])
            ->set('namaBaru', 'Siti Aminah')
            ->set('nisBaru', '0056781235')
            ->set('jkBaru', 'P')
            ->call('tambahSatu')
            ->assertHasNoErrors()
            ->assertSee('Siti Aminah');

        expect($kelas->siswa()->count())->toBe(1);
    });

    it('resets a PIN and shows the new one once', function (): void {
        $kelas = Classroom::factory()->create();
        $siswa = User::factory()->siswa()->pin('111222')->create(['pin_kartu' => '111222']);
        $kelas->enrollments()->create(['user_id' => $siswa->id]);

        $komponen = Livewire::actingAs($kelas->guru)
            ->test(DetailKelas::class, ['classroom' => $kelas])
            ->call('aturUlangPin', $siswa->id);

        $pinBaru = $komponen->get('pinBaru')[$siswa->id];
        $siswa->refresh();

        expect($pinBaru)->toMatch('/^\d{6}$/')->not->toBe('111222')
            ->and($siswa->pin_kartu)->toBe($pinBaru)
            ->and(Hash::check($pinBaru, $siswa->password))->toBeTrue();
        $komponen->assertSee($pinBaru);
    });

    it('forbids a teacher from opening another teacher\'s class', function (): void {
        $kelas = Classroom::factory()->create();

        actingAs(User::factory()->guru()->create())
            ->get(route('guru.kelas', $kelas))
            ->assertForbidden();
    });
});

describe('G-05 kartu PIN', function (): void {
    it('prints a card per active student with NIS and PIN', function (): void {
        $kelas = Classroom::factory()->create();
        $aktif = User::factory()->siswa()->create(['nama' => 'Reza Pratama', 'username' => '0056781234', 'pin_kartu' => '482913']);
        $keluar = User::factory()->siswa()->create(['nama' => 'Sudah Keluar', 'pin_kartu' => '999999']);
        $kelas->enrollments()->create(['user_id' => $aktif->id]);
        $kelas->enrollments()->create(['user_id' => $keluar->id, 'status' => 'keluar']);

        actingAs($kelas->guru)
            ->get(route('guru.kartu-pin', $kelas))
            ->assertOk()
            ->assertSee('Reza Pratama')
            ->assertSee('0056781234')
            ->assertSee('482913')
            ->assertDontSee('Sudah Keluar')
            ->assertSee('@page', escape: false);
    });

    it('forbids printing another teacher\'s class', function (): void {
        actingAs(User::factory()->guru()->create())
            ->get(route('guru.kartu-pin', Classroom::factory()->create()))
            ->assertForbidden();
    });
});
