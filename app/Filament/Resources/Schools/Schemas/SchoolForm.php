<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama sekolah')
                ->required()
                ->maxLength(255),
            TextInput::make('npsn')
                ->label('NPSN')
                ->maxLength(20),
            Select::make('kabupaten_kota')
                ->label('Kabupaten/Kota')
                ->options(array_combine(
                    $daerah = ['Kota Tasikmalaya', 'Kab. Tasikmalaya', 'Ciamis', 'Banjar', 'Garut', 'Pangandaran', 'Lainnya'],
                    $daerah,
                ))
                ->required(),
            TextInput::make('alamat')
                ->label('Alamat')
                ->maxLength(255),
            TextInput::make('kontak_guru')
                ->label('Kontak guru mitra')
                ->helperText('Nama/surel guru, bukan nomor HP pribadi siswa.')
                ->maxLength(255),
        ]);
    }
}
