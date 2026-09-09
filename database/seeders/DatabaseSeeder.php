<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Akun admin awal untuk panel /admin. Ganti kata sandinya sebelum dipakai di sekolah.
        User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin GEULIS',
                'email' => 'admin@geulis.test',
                'password' => Hash::make('password'),
            ],
        );

        // Kerangka 5 pertemuan x 7 unit; isi kontennya lewat panel admin, bukan seeder.
        $this->call(MeetingSeeder::class);
    }
}
