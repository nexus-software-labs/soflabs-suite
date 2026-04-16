<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Suppliers;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Inventory\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Inventory\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Inventory\Suppliers\Pages\ViewSupplier;
use App\Filament\Resources\Inventory\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Inventory\Suppliers\Schemas\SupplierInfolist;
use App\Filament\Resources\Inventory\Suppliers\Tables\SuppliersTable;
use App\Models\Inventory\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupplierInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view' => ViewSupplier::route('/{record}'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
