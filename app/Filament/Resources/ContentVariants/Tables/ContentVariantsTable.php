<?php

namespace App\Filament\Resources\ContentVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lessonUnit.meeting.urutan')->label('Pert.')->sortable(),
                TextColumn::make('lessonUnit.judul')->label('Unit')->searchable(),
                TextColumn::make('level')->label('Level')->badge(),
                TextColumn::make('modus')->label('Modus')->badge(),
                TextColumn::make('judul')->label('Judul varian')->searchable()->wrap(),
                TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'siap' => 'success', 'revisi' => 'warning', default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draf' => 'Draf', 'revisi' => 'Revisi', 'siap' => 'Siap']),
                SelectFilter::make('level')->options(['*' => '*', 'L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3']),
                SelectFilter::make('lesson_unit_id')->label('Unit')->relationship('lessonUnit', 'judul'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
