<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lessonUnit.meeting.urutan')->label('Pert.')->sortable(),
                TextColumn::make('lessonUnit.judul')->label('Unit'),
                TextColumn::make('tipe')->label('Tipe')->badge(),
                TextColumn::make('judul')->label('Judul')->searchable(),
                TextColumn::make('level')->label('Level')->badge(),
                TextColumn::make('attempts_count')->label('Percobaan')->counts('attempts'),
            ])
            ->filters([
                SelectFilter::make('tipe')->options(['kuis' => 'Kuis', 'geogebra' => 'GeoGebra', 'motif_builder' => 'Motif Builder', 'unggah' => 'Unggah', 'refleksi' => 'Refleksi']),
            ])
            ->recordActions([EditAction::make()]);
    }
}
