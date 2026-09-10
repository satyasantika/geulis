<?php

namespace App\Enums;

/**
 * Enam peran pengguna (cetak biru §2.3). Nilainya sama persis dengan kolom
 * `roles.nama` supaya `role:siswa` pada rute, seeder, dan uji memakai satu
 * sumber kebenaran.
 */
enum Peran: string
{
    case Siswa = 'siswa';
    case Guru = 'guru';
    case Validator = 'validator';
    case Observer = 'observer';
    case Peneliti = 'peneliti';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Siswa => 'Siswa',
            self::Guru => 'Guru',
            self::Validator => 'Validator (Ahli)',
            self::Observer => 'Observer',
            self::Peneliti => 'Peneliti',
            self::Admin => 'Admin',
        };
    }

    /**
     * Peran yang boleh membuka panel Filament (/admin). Guru dan siswa punya
     * layar sendiri; validator dan observer masuk lewat tautan bertanda.
     *
     * @return list<self>
     */
    public static function pengelolaPanel(): array
    {
        return [self::Admin, self::Peneliti];
    }
}
