<?php

namespace App\Filament\Resources\CtTests\Pages;

use App\Filament\Resources\CtTests\CtTestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCtTest extends EditRecord
{
    protected static string $resource = CtTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
