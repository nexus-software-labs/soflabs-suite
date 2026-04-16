<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\UnitConversions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitConversionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversión de unidad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('fromUnit.name')->label('Desde')->placeholder('—'),
                                TextEntry::make('toUnit.name')->label('Hacia')->placeholder('—'),
                                TextEntry::make('factor')->label('Factor')->numeric(decimalPlaces: 8),
                            ]),
                    ]),
            ]);
    }
}
