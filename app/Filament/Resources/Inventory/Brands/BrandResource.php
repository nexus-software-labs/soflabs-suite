<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Brands;

use App\Filament\Resources\Inventory\Brands\Pages\CreateBrand;
use App\Filament\Resources\Inventory\Brands\Pages\EditBrand;
use App\Filament\Resources\Inventory\Brands\Pages\ListBrands;
use App\Filament\Resources\Inventory\Brands\Pages\ViewBrand;
use App\Filament\Resources\Inventory\Brands\Schemas\BrandForm;
use App\Filament\Resources\Inventory\Brands\Schemas\BrandInfolist;
use App\Filament\Resources\Inventory\Brands\Tables\BrandsTable;
use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Models\Inventory\Brand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Marcas';

    protected static ?string $modelLabel = 'Marca';

    protected static ?string $pluralModelLabel = 'Marcas';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return BrandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BrandInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandsTable::configure($table);
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'view' => ViewBrand::route('/{record}'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
