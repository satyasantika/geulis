<?php

namespace App\Filament\Resources\SimulationRuns\Schemas;

use App\Services\Simulasi\PembuatSimulasi;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SimulationRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('jumlah_kelas')
                ->label('Jumlah kelas')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(PembuatSimulasi::MAKS_KELAS)
                ->default(1),
            TextInput::make('jumlah_guru')
                ->label('Jumlah guru')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(PembuatSimulasi::MAKS_GURU)
                ->default(1),
            TextInput::make('jumlah_siswa')
                ->label('Jumlah siswa')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(PembuatSimulasi::MAKS_SISWA)
                ->default(4)
                ->helperText('Akun sementara. Guru dan siswa masuk lewat QR berurutan, tanpa sandi. Hapus sesi ini setelah selesai.'),
        ]);
    }
}
