<?php

namespace App\Filament\Resources\CtItems\Pages;

use App\Filament\Resources\CtItems\CtItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCtItems extends ListRecords
{
    protected static string $resource = CtItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
