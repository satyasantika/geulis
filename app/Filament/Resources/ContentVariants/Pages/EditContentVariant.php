<?php

namespace App\Filament\Resources\ContentVariants\Pages;

use App\Filament\Resources\ContentVariants\ContentVariantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentVariant extends EditRecord
{
    protected static string $resource = ContentVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
