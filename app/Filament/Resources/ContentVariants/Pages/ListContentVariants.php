<?php

namespace App\Filament\Resources\ContentVariants\Pages;

use App\Filament\Resources\ContentVariants\ContentVariantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentVariants extends ListRecords
{
    protected static string $resource = ContentVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
