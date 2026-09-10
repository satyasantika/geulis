<?php

namespace App\Filament\Resources\Classrooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school.nama')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guru.nama')
                    ->label('Guru')
                    ->searchable(),
                TextColumn::make('tahun_ajaran')
                    ->label('Tahun ajaran'),
                TextColumn::make('kelompok_riset')
                    ->label('Kelompok')
                    ->badge(),
                TextColumn::make('enrollments_count')
                    ->label('Siswa')
                    ->counts('enrollments'),
                TextColumn::make('kode_gabung')
                    ->label('Kode gabung')
                    ->fontFamily('mono'),
                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelompok_riset')
                    ->label('Kelompok riset')
                    ->options([
                        'eksperimen' => 'Eksperimen',
                        'kontrol' => 'Kontrol',
                        'uji_terbatas' => 'Uji terbatas',
                        'non_riset' => 'Non-riset',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
