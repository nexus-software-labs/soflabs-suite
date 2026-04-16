<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\SupplierProducts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto por proveedor')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('supplier.name')->label('Proveedor')->placeholder('—'),
                                TextEntry::make('product.name')->label('Producto')->placeholder('—'),
                                TextEntry::make('product.sku')->label('SKU')->placeholder('—'),
                                TextEntry::make('unit.name')->label('Unidad')->placeholder('—'),
                                TextEntry::make('price')->label('Precio')->money('USD')->placeholder('—'),
                                TextEntry::make('supplier_sku')->label('SKU proveedor')->placeholder('—'),
                                TextEntry::make('status')->label('Estado')->badge(),
                            ]),
                    ]),
            ]);
    }
}
