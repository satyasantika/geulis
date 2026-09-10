<?php

namespace App\Filament\Resources\CtItems;

use App\Filament\Resources\CtItems\Pages\CreateCtItem;
use App\Filament\Resources\CtItems\Pages\EditCtItem;
use App\Filament\Resources\CtItems\Pages\ListCtItems;
use App\Filament\Resources\CtItems\Schemas\CtItemForm;
use App\Filament\Resources\CtItems\Tables\CtItemsTable;
use App\Models\CtItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CtItemResource extends Resource
{
    protected static ?string $model = CtItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Butir tes CT';

    protected static ?string $modelLabel = 'butir tes CT';

    protected static ?string $pluralModelLabel = 'butir tes CT';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return CtItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CtItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCtItems::route('/'),
            'create' => CreateCtItem::route('/create'),
            'edit' => EditCtItem::route('/{record}/edit'),
        ];
    }
}
