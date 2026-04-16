<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proveedor')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')->label('Nombre'),
                                TextEntry::make('supplier_type')->label('Tipo')->badge(),
                                TextEntry::make('status')->label('Estado')->badge(),
                                TextEntry::make('tax_id')->label('Tax ID')->placeholder('—'),
                                TextEntry::make('country_code')->label('País')->placeholder('—'),
                                TextEntry::make('payment_terms')->label('Términos')->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
