<?php

namespace App\Filament\Resources\CulturalAssets\Pages;

use App\Filament\Resources\CulturalAssets\CulturalAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCulturalAsset extends EditRecord
{
    protected static string $resource = CulturalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
