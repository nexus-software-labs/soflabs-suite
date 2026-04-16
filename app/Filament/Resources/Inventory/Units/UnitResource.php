<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Units;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Units\Pages\CreateUnit;
use App\Filament\Resources\Inventory\Units\Pages\EditUnit;
use App\Filament\Resources\Inventory\Units\Pages\ListUnits;
use App\Filament\Resources\Inventory\Units\Pages\ViewUnit;
use App\Filament\Resources\Inventory\Units\Schemas\UnitForm;
use App\Filament\Resources\Inventory\Units\Schemas\UnitInfolist;
use App\Filament\Resources\Inventory\Units\Tables\UnitsTable;
use App\Models\Inventory\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Unit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Unidades';

    protected static ?string $modelLabel = 'Unidad';

    protected static ?string $pluralModelLabel = 'Unidades';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return UnitForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UnitInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitsTable::configure($table);
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
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'view' => ViewUnit::route('/{record}'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
