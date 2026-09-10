<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Enam peran tetap. Idempoten: aman dijalankan berulang, tidak menggandakan
 * baris, dan hanya memperbarui label bila berubah.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Peran::cases() as $peran) {
            Role::query()->updateOrCreate(
                ['nama' => $peran->value],
                ['label' => $peran->label()],
            );
        }
    }
}
