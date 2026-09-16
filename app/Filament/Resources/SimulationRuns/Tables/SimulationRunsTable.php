<?php

namespace App\Filament\Resources\SimulationRuns\Tables;

use App\Models\SimulationRun;
use App\Services\Simulasi\PenghapusSimulasi;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SimulationRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('No')->sortable(),
                TextColumn::make('jumlah_kelas')->label('Kelas'),
                TextColumn::make('jumlah_guru')->label('Guru'),
                TextColumn::make('jumlah_siswa')->label('Siswa'),
                TextColumn::make('masuk')
                    ->label('Sudah masuk')
                    ->state(fn (SimulationRun $record): string => $record->tokens()->whereNotNull('diklaim_pada')->count().' / '.($record->jumlah_guru + $record->jumlah_siswa)),
                TextColumn::make('created_at')->label('Dibuat')->since()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('layarQr')
                    ->label('Layar QR')
                    ->url(fn (SimulationRun $record): string => $record->tautanLayar())
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus simulasi ini?')
                    ->modalDescription('Kelas dan akun sementara ikut terhapus. Data penelitian tidak tersentuh.')
                    ->action(fn (SimulationRun $record) => app(PenghapusSimulasi::class)->hapus($record)),
            ]);
    }
}
