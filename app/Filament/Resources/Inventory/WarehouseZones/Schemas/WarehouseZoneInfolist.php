<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\WarehouseZones\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseZoneInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zona de bodega')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('warehouse.name')->label('Bodega')->placeholder('—'),
                                TextEntry::make('name')->label('Zona'),
                                TextEntry::make('storage_condition')->label('Condición')->placeholder('—'),
                                TextEntry::make('status')->label('Estado')->badge(),
                                TextEntry::make('description')
                                    ->label('Descripción')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
