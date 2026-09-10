<?php

namespace App\Filament\Resources\CulturalAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Atribusi wajib (aturan #6) — formulir menolak, dan model menolak lagi.
 */
class CulturalAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama_motif')->label('Nama motif')->required()->maxLength(255),
            Select::make('artefak')->label('Artefak')->options(['batik' => 'Batik', 'payung_geulis' => 'Payung geulis', 'anyaman' => 'Anyaman'])->required(),
            TextInput::make('perajin_sumber')->label('Perajin / sumber')->required()->maxLength(255),
            TextInput::make('lokasi')->label('Lokasi dokumentasi')->required()->maxLength(255),
            DatePicker::make('tanggal_dokumentasi')->label('Tanggal dokumentasi')->required(),
            Toggle::make('izin_diperoleh')->label('Izin dari perajin/pemilik sudah diperoleh')->accepted()
                ->validationMessages(['accepted' => 'Aset tidak boleh disimpan sebelum izin diperoleh.']),
            FileUpload::make('berkas')->label('Foto/video')->directory('aset-budaya')->disk('public')
                ->acceptedFileTypes(['image/webp', 'image/jpeg', 'image/png', 'video/mp4'])->maxSize(5120)->required()->columnSpanFull(),
            Textarea::make('catatan_matematis')->label('Catatan matematis')->rows(3)->required()
                ->helperText('Konsep transformasi yang BENAR-BENAR ada pada artefak, bukan yang diada-adakan.')->columnSpanFull(),
        ])->columns(2);
    }
}
