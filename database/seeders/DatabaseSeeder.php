<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([RoleSeeder::class, ReadinessItemSeeder::class]);

        // Akun admin awal untuk panel /admin. Ganti kata sandinya sebelum dipakai di sekolah.
        $admin = User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin GEULIS',
                'email' => 'admin@geulis.test',
                'password' => Hash::make('password'),
            ],
        );

        // Satu akun boleh memegang lebih dari satu peran (cetak biru §2.3).
        $admin->berikanPeran(Peran::Admin, Peran::Peneliti);

        // Kerangka 5 pertemuan x 7 unit; isi kontennya lewat panel admin, bukan seeder.
        // Motif sasaran Motif Builder ikut kerangka: strukturnya bagian dari mesin penilaian.
        $this->call([MeetingSeeder::class, MotifSasaranSeeder::class, CtTestSeeder::class, RubricSeeder::class, InstrumenPenelitianSeeder::class]);
    }
}
