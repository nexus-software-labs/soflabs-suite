<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Warehouses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bodega')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')->label('Nombre'),
                                TextEntry::make('warehouse_type')->label('Tipo')->badge(),
                                TextEntry::make('status')->label('Estado')->badge(),
                                TextEntry::make('location')->label('Ubicación')->placeholder('—'),
                                TextEntry::make('responsibleUser.name')->label('Responsable')->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
