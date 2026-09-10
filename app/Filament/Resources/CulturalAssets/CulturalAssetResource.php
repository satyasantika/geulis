<?php

namespace App\Filament\Resources\CulturalAssets;

use App\Filament\Resources\CulturalAssets\Pages\CreateCulturalAsset;
use App\Filament\Resources\CulturalAssets\Pages\EditCulturalAsset;
use App\Filament\Resources\CulturalAssets\Pages\ListCulturalAssets;
use App\Filament\Resources\CulturalAssets\Schemas\CulturalAssetForm;
use App\Filament\Resources\CulturalAssets\Tables\CulturalAssetsTable;
use App\Models\CulturalAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CulturalAssetResource extends Resource
{
    protected static ?string $model = CulturalAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Aset budaya';

    protected static ?string $modelLabel = 'aset budaya';

    protected static ?string $pluralModelLabel = 'aset budaya';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return CulturalAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CulturalAssetsTable::configure($table);
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
            'index' => ListCulturalAssets::route('/'),
            'create' => CreateCulturalAsset::route('/create'),
            'edit' => EditCulturalAsset::route('/{record}/edit'),
        ];
    }
}
