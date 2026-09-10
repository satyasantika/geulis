<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Enums\Peran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_id')
                ->label('Sekolah')
                ->relationship('school', 'nama')
                ->required(),
            Select::make('guru_id')
                ->label('Guru pengampu')
                ->relationship(
                    'guru',
                    'nama',
                    fn (Builder $query) => $query->whereHas('roles', fn (Builder $r) => $r->where('nama', Peran::Guru->value)),
                )
                ->searchable()
                ->preload(),
            TextInput::make('nama')
                ->label('Nama kelas')
                ->placeholder('XI MIPA 3')
                ->required()
                ->maxLength(50),
            TextInput::make('tahun_ajaran')
                ->label('Tahun ajaran')
                ->placeholder('2026/2027')
                ->required()
                ->maxLength(12),
            Select::make('kelompok_riset')
                ->label('Kelompok riset')
                ->helperText('Melekat pada kelas utuh, bukan pada siswa (kuasi-eksperimen).')
                ->options([
                    'eksperimen' => 'Eksperimen',
                    'kontrol' => 'Kontrol',
                    'uji_terbatas' => 'Uji terbatas',
                    'non_riset' => 'Non-riset',
                ])
                ->default('non_riset')
                ->required(),
            TextInput::make('kode_gabung')
                ->label('Kode gabung')
                ->helperText('Kosongkan untuk dibuat otomatis.')
                ->maxLength(10)
                ->unique(ignoreRecord: true),
            Toggle::make('aktif')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
