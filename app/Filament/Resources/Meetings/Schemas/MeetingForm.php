<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('urutan')->label('Urutan')->numeric()->minValue(1)->maxValue(5)->required()->disabledOn('edit'),
            TextInput::make('judul')->label('Judul')->required()->maxLength(255),
            Select::make('materi')->label('Materi')->options([
                'translasi' => 'Translasi', 'refleksi' => 'Refleksi', 'rotasi' => 'Rotasi', 'dilatasi' => 'Dilatasi', 'komposisi' => 'Komposisi',
            ])->required(),
            Select::make('artefak_utama')->label('Artefak utama')->options([
                'batik' => 'Batik', 'payung_geulis' => 'Payung geulis', 'anyaman' => 'Anyaman', 'pilihan_siswa' => 'Pilihan siswa',
            ])->required(),
            Select::make('fokus_ct')->label('Fokus CT')->options([
                'dekomposisi' => 'Dekomposisi', 'pengenalan_pola' => 'Pengenalan pola', 'abstraksi' => 'Abstraksi', 'algoritma' => 'Algoritma',
            ])->required(),
            Textarea::make('capaian_pembelajaran')->label('Capaian pembelajaran')->rows(3)->required()->columnSpanFull(),
            Toggle::make('terbit')->label('Terbit (dapat dibuka siswa)')->helperText('Pertemuan berikutnya hanya terbuka bila pertemuan sebelumnya sudah diselesaikan siswa.'),
        ]);
    }
}
