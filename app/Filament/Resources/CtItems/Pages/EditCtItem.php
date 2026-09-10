<?php

namespace App\Filament\Resources\CtItems\Pages;

use App\Filament\Resources\CtItems\CtItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCtItem extends EditRecord
{
    protected static string $resource = CtItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
