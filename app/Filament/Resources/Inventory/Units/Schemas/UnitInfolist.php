<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Units\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unidad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')->label('Nombre'),
                                TextEntry::make('abbreviation')->label('Abreviación'),
                                TextEntry::make('unit_type')->label('Tipo')->badge(),
                                IconEntry::make('is_system')->label('Sistema')->boolean(),
                                TextEntry::make('status')->label('Estado')->badge(),
                            ]),
                    ]),
            ]);
    }
}
