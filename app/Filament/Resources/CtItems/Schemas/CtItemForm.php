<?php

namespace App\Filament\Resources\CtItems\Schemas;

use App\Models\CtItem;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CtItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ct_test_id')->label('Tes')->relationship('test', 'judul')->required(),
            TextInput::make('urutan')->label('Urutan')->numeric()->minValue(1)->required(),
            Select::make('indikator')->label('Indikator CT')->options(CtItem::INDIKATOR)->required(),
            Select::make('tipe')->label('Tipe')->options(['pg' => 'Pilihan ganda', 'isian' => 'Isian singkat', 'uraian' => 'Uraian'])->required()->live(),
            Textarea::make('stimulus')->label('Stimulus (konteks budaya)')->rows(2)->columnSpanFull(),
            Select::make('cultural_asset_id')->label('Aset budaya (opsional)')->relationship('culturalAsset', 'nama_motif')->searchable()->preload(),
            TextInput::make('skor_maks')->label('Skor maksimum')->numeric()->default(4)->required(),
            Textarea::make('pertanyaan')->label('Pertanyaan')->rows(3)->required()->columnSpanFull(),
            TagsInput::make('pilihan')->label('Pilihan jawaban')->visible(fn (Get $get): bool => $get('tipe') === 'pg')->columnSpanFull(),
            TextInput::make('kunci')->label('Kunci (teks pilihan / jawaban isian)')->visible(fn (Get $get): bool => $get('tipe') !== 'uraian')->columnSpanFull(),
            KeyValue::make('rubrik_butir')->label('Rubrik butir (tingkat → deskriptor)')->keyLabel('Tingkat')->valueLabel('Deskriptor')
                ->visible(fn (Get $get): bool => $get('tipe') === 'uraian')->columnSpanFull(),
        ])->columns(2);
    }
}
