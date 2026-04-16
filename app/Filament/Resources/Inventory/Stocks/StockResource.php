<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Stocks;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Stocks\Pages\CreateStock;
use App\Filament\Resources\Inventory\Stocks\Pages\EditStock;
use App\Filament\Resources\Inventory\Stocks\Pages\ListStocks;
use App\Filament\Resources\Inventory\Stocks\Pages\ViewStock;
use App\Filament\Resources\Inventory\Stocks\Schemas\StockForm;
use App\Filament\Resources\Inventory\Stocks\Schemas\StockInfolist;
use App\Filament\Resources\Inventory\Stocks\Tables\StocksTable;
use App\Models\Inventory\Stock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Stock';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Stock';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Stock y trazabilidad';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return StockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
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
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            'view' => ViewStock::route('/{record}'),
            'edit' => EditStock::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
