<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Warehouses;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\Inventory\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\Inventory\Warehouses\Pages\ListWarehouses;
use App\Filament\Resources\Inventory\Warehouses\Pages\ViewWarehouse;
use App\Filament\Resources\Inventory\Warehouses\Schemas\WarehouseForm;
use App\Filament\Resources\Inventory\Warehouses\Schemas\WarehouseInfolist;
use App\Filament\Resources\Inventory\Warehouses\Tables\WarehousesTable;
use App\Models\Inventory\Warehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Warehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Bodegas';

    protected static ?string $modelLabel = 'Bodega';

    protected static ?string $pluralModelLabel = 'Bodegas';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WarehouseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view' => ViewWarehouse::route('/{record}'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
}
