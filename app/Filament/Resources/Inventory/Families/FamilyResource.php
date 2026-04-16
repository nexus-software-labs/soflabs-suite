<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Families;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Families\Pages\CreateFamily;
use App\Filament\Resources\Inventory\Families\Pages\EditFamily;
use App\Filament\Resources\Inventory\Families\Pages\ListFamilies;
use App\Filament\Resources\Inventory\Families\Pages\ViewFamily;
use App\Filament\Resources\Inventory\Families\Schemas\FamilyForm;
use App\Filament\Resources\Inventory\Families\Schemas\FamilyInfolist;
use App\Filament\Resources\Inventory\Families\Tables\FamiliesTable;
use App\Models\Inventory\Family;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FamilyResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Family::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Familias';

    protected static ?string $modelLabel = 'Familia';

    protected static ?string $pluralModelLabel = 'Familias';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 120;

    public static function form(Schema $schema): Schema
    {
        return FamilyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FamilyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FamiliesTable::configure($table);
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
            'index' => ListFamilies::route('/'),
            'create' => CreateFamily::route('/create'),
            'view' => ViewFamily::route('/{record}'),
            'edit' => EditFamily::route('/{record}/edit'),
        ];
    }
}
