<?php

namespace App\Filament\Resources\CulturalAssets\Pages;

use App\Filament\Resources\CulturalAssets\CulturalAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCulturalAssets extends ListRecords
{
    protected static string $resource = CulturalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
