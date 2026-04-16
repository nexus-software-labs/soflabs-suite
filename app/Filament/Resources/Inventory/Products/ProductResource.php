<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Products;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Products\Pages\CreateProduct;
use App\Filament\Resources\Inventory\Products\Pages\EditProduct;
use App\Filament\Resources\Inventory\Products\Pages\ListProducts;
use App\Filament\Resources\Inventory\Products\Pages\ViewProduct;
use App\Filament\Resources\Inventory\Products\Schemas\ProductForm;
use App\Filament\Resources\Inventory\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Inventory\Products\Tables\ProductsTable;
use App\Models\Inventory\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
