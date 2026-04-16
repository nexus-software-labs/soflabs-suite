<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Brands\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Marca')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')->label('Nombre'),
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
