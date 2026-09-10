<?php

namespace App\Filament\Resources\ContentVariants;

use App\Filament\Resources\ContentVariants\Pages\CreateContentVariant;
use App\Filament\Resources\ContentVariants\Pages\EditContentVariant;
use App\Filament\Resources\ContentVariants\Pages\ListContentVariants;
use App\Filament\Resources\ContentVariants\Schemas\ContentVariantForm;
use App\Filament\Resources\ContentVariants\Tables\ContentVariantsTable;
use App\Models\ContentVariant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentVariantResource extends Resource
{
    protected static ?string $model = ContentVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Varian konten';

    protected static ?string $modelLabel = 'varian konten';

    protected static ?string $pluralModelLabel = 'varian konten';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return ContentVariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentVariantsTable::configure($table);
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
            'index' => ListContentVariants::route('/'),
            'create' => CreateContentVariant::route('/create'),
            'edit' => EditContentVariant::route('/{record}/edit'),
        ];
    }
}
