<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\UnitConversions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UnitConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversión de unidad')
                    ->schema([
                        Select::make('from_unit_id')
                            ->label('Desde unidad')
                            ->relationship(
                                name: 'fromUnit',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('to_unit_id')
                            ->label('Hacia unidad')
                            ->relationship(
                                name: 'toUnit',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('factor')
                            ->label('Factor')
                            ->required()
                            ->numeric()
                            ->minValue(0.00000001),
                    ])
                    ->columns(2),
            ]);
    }
}
