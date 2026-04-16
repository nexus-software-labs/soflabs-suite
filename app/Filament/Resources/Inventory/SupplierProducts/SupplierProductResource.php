<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\SupplierProducts;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\SupplierProducts\Pages\CreateSupplierProduct;
use App\Filament\Resources\Inventory\SupplierProducts\Pages\EditSupplierProduct;
use App\Filament\Resources\Inventory\SupplierProducts\Pages\ListSupplierProducts;
use App\Filament\Resources\Inventory\SupplierProducts\Pages\ViewSupplierProduct;
use App\Filament\Resources\Inventory\SupplierProducts\Schemas\SupplierProductForm;
use App\Filament\Resources\Inventory\SupplierProducts\Schemas\SupplierProductInfolist;
use App\Filament\Resources\Inventory\SupplierProducts\Tables\SupplierProductsTable;
use App\Models\Inventory\SupplierProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierProductResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = SupplierProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Productos proveedor';

    protected static ?string $modelLabel = 'Producto proveedor';

    protected static ?string $pluralModelLabel = 'Productos proveedor';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 150;

    public static function form(Schema $schema): Schema
    {
        return SupplierProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupplierProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierProductsTable::configure($table);
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
            'index' => ListSupplierProducts::route('/'),
            'create' => CreateSupplierProduct::route('/create'),
            'view' => ViewSupplierProduct::route('/{record}'),
            'edit' => EditSupplierProduct::route('/{record}/edit'),
        ];
    }
}
