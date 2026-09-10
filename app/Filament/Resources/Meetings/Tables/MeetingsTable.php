<?php

namespace App\Filament\Resources\Meetings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')->label('#')->sortable(),
                TextColumn::make('judul')->label('Judul')->searchable(),
                TextColumn::make('materi')->label('Materi')->badge(),
                TextColumn::make('fokus_ct')->label('Fokus CT'),
                TextColumn::make('content_variants_count')->label('Varian')->counts('contentVariants'),
                IconColumn::make('terbit')->label('Terbit')->boolean(),
            ])
            ->defaultSort('urutan')
            ->recordActions([EditAction::make()]);
    }
}
