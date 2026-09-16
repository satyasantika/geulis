<?php

namespace App\Filament\Resources\SimulationRuns\Pages;

use App\Filament\Resources\SimulationRuns\SimulationRunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSimulationRuns extends ListRecords
{
    protected static string $resource = SimulationRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat simulasi'),
        ];
    }
}
