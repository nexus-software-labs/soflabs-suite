<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\WarehouseZones;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\WarehouseZones\Pages\CreateWarehouseZone;
use App\Filament\Resources\Inventory\WarehouseZones\Pages\EditWarehouseZone;
use App\Filament\Resources\Inventory\WarehouseZones\Pages\ListWarehouseZones;
use App\Filament\Resources\Inventory\WarehouseZones\Pages\ViewWarehouseZone;
use App\Filament\Resources\Inventory\WarehouseZones\Schemas\WarehouseZoneForm;
use App\Filament\Resources\Inventory\WarehouseZones\Schemas\WarehouseZoneInfolist;
use App\Filament\Resources\Inventory\WarehouseZones\Tables\WarehouseZonesTable;
use App\Models\Inventory\WarehouseZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseZoneResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = WarehouseZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Zonas de bodega';

    protected static ?string $modelLabel = 'Zona de bodega';

    protected static ?string $pluralModelLabel = 'Zonas de bodega';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 160;

    public static function form(Schema $schema): Schema
    {
        return WarehouseZoneForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WarehouseZoneInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehouseZonesTable::configure($table);
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
            'index' => ListWarehouseZones::route('/'),
            'create' => CreateWarehouseZone::route('/create'),
            'view' => ViewWarehouseZone::route('/{record}'),
            'edit' => EditWarehouseZone::route('/{record}/edit'),
        ];
    }
}
