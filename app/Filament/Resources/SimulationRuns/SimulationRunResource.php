<?php

namespace App\Filament\Resources\SimulationRuns;

use App\Enums\Peran;
use App\Filament\Resources\SimulationRuns\Pages\CreateSimulationRun;
use App\Filament\Resources\SimulationRuns\Pages\ListSimulationRuns;
use App\Filament\Resources\SimulationRuns\Schemas\SimulationRunForm;
use App\Filament\Resources\SimulationRuns\Tables\SimulationRunsTable;
use App\Models\SimulationRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SimulationRunResource extends Resource
{
    protected static ?string $model = SimulationRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlay;

    protected static ?string $navigationLabel = 'Simulasi';

    protected static ?string $modelLabel = 'simulasi';

    protected static ?string $pluralModelLabel = 'simulasi';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()?->punyaPeran(Peran::Admin) === true;
    }

    public static function form(Schema $schema): Schema
    {
        return SimulationRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SimulationRunsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSimulationRuns::route('/'),
            'create' => CreateSimulationRun::route('/create'),
        ];
    }
}
