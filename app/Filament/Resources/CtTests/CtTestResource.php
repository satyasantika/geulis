<?php

namespace App\Filament\Resources\CtTests;

use App\Filament\Resources\CtTests\Pages\CreateCtTest;
use App\Filament\Resources\CtTests\Pages\EditCtTest;
use App\Filament\Resources\CtTests\Pages\ListCtTests;
use App\Filament\Resources\CtTests\Schemas\CtTestForm;
use App\Filament\Resources\CtTests\Tables\CtTestsTable;
use App\Models\CtTest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CtTestResource extends Resource
{
    protected static ?string $model = CtTest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Tes CT';

    protected static ?string $modelLabel = 'tes CT';

    protected static ?string $pluralModelLabel = 'tes CT';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return CtTestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CtTestsTable::configure($table);
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
            'index' => ListCtTests::route('/'),
            'create' => CreateCtTest::route('/create'),
            'edit' => EditCtTest::route('/{record}/edit'),
        ];
    }
}
