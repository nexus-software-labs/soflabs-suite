<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sku')->label('SKU'),
                                TextEntry::make('name')->label('Nombre'),
                                TextEntry::make('status')->label('Estado')->badge(),
                                TextEntry::make('group.name')->label('Grupo')->placeholder('—'),
                                TextEntry::make('brand.name')->label('Marca')->placeholder('—'),
                                TextEntry::make('valuation_method')->label('Valoración')->placeholder('—'),
                                TextEntry::make('purchaseUnit.name')->label('Unidad compra')->placeholder('—'),
                                TextEntry::make('stockUnit.name')->label('Unidad stock')->placeholder('—'),
                                TextEntry::make('salesUnit.name')->label('Unidad venta')->placeholder('—'),
                                TextEntry::make('minimum_stock')
                                    ->label('Stock mínimo')
                                    ->numeric(decimalPlaces: 4),
                            ]),
                    ]),
            ]);
    }
}
