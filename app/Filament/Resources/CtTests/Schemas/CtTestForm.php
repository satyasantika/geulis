<?php

namespace App\Filament\Resources\CtTests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CtTestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('jenis')->label('Jenis')->options(['pretest' => 'Pretest', 'posttest' => 'Posttest'])->required(),
            TextInput::make('judul')->label('Judul')->required()->maxLength(255),
            TextInput::make('durasi_menit')->label('Durasi (menit)')->numeric()->minValue(5)->maxValue(180)->default(60)->required(),
            Toggle::make('aktif')->label('Aktif (dapat dikerjakan siswa)')
                ->helperText('Pretest diaktifkan sebelum Pertemuan 1; nonaktifkan setelah semua kelas mengerjakan. Pretest yang terlewat tidak bisa diambil ulang setelah pembelajaran dimulai.'),
        ]);
    }
}
