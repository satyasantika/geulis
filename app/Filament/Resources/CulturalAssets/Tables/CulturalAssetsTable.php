<?php

namespace App\Filament\Resources\CulturalAssets\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CulturalAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('berkas')->label('')->disk('public'),
                TextColumn::make('nama_motif')->label('Motif')->searchable(),
                TextColumn::make('artefak')->label('Artefak')->badge(),
                TextColumn::make('perajin_sumber')->label('Perajin/sumber')->searchable(),
                TextColumn::make('lokasi')->label('Lokasi'),
                TextColumn::make('tanggal_dokumentasi')->label('Tanggal')->date(),
                IconColumn::make('izin_diperoleh')->label('Izin')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
