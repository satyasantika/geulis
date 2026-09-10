<?php

namespace App\Filament\Resources\CtTests\Pages;

use App\Filament\Resources\CtTests\CtTestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCtTests extends ListRecords
{
    protected static string $resource = CtTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
