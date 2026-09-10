<?php

namespace App\Filament\Resources\CtItems\Tables;

use App\Models\CtItem;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CtItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('test.jenis')->label('Tes')->badge(),
                TextColumn::make('urutan')->label('#')->sortable(),
                TextColumn::make('indikator')->label('Ind.')->badge(),
                TextColumn::make('tipe')->label('Tipe'),
                TextColumn::make('pertanyaan')->label('Pertanyaan')->limit(70)->searchable(),
                TextColumn::make('skor_maks')->label('Maks'),
            ])
            ->filters([
                SelectFilter::make('ct_test_id')->label('Tes')->relationship('test', 'judul'),
                SelectFilter::make('indikator')->options(CtItem::INDIKATOR),
            ])
            ->defaultSort('urutan')
            ->recordActions([EditAction::make()]);
    }
}
