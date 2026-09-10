<?php

namespace App\Filament\Resources\CtTests\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CtTestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('judul')->label('Judul')->searchable(),
                TextColumn::make('durasi_menit')->label('Menit'),
                TextColumn::make('items_count')->label('Butir')->counts('items'),
                TextColumn::make('scores_count')->label('Peserta')->counts('scores'),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
