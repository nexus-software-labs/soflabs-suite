<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OutboundRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitud')
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Bodega')
                            ->relationship(
                                name: 'warehouse',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('request_number')
                            ->label('Número de solicitud')
                            ->maxLength(80),
                        TextInput::make('requested_by_name')
                            ->label('Solicitado por')
                            ->maxLength(120),
                        TextInput::make('destination')
                            ->label('Destino')
                            ->maxLength(150),
                    ])
                    ->columns(2),
                Section::make('Líneas')
                    ->schema([
                        Repeater::make('lines')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('requested_quantity')
                                    ->label('Cantidad solicitada')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.0001),
                            ])
                            ->defaultItems(1)
                            ->columns(2)
                            ->addActionLabel('Agregar línea')
                            ->reorderable(false)
                            ->required(),
                    ]),
            ]);
    }
}
