<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Stocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('product.sku')
                                    ->label('SKU')
                                    ->placeholder('—'),
                                TextEntry::make('product.name')
                                    ->label('Producto')
                                    ->placeholder('—'),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega')
                                    ->placeholder('—'),
                                TextEntry::make('quantity')
                                    ->label('Existencia')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('reserved_quantity')
                                    ->label('Reservado')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('updated_at')
                                    ->label('Actualizado')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
