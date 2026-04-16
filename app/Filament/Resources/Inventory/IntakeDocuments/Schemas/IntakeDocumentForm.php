<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class IntakeDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documento')
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
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship(
                                name: 'supplier',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload(),
                        TextInput::make('document_number')
                            ->label('Número de documento')
                            ->maxLength(80),
                        DatePicker::make('document_date')
                            ->label('Fecha de documento'),
                        TextInput::make('currency_code')
                            ->label('Moneda')
                            ->default('USD')
                            ->required()
                            ->maxLength(10),
                    ])
                    ->columns(2),
                Section::make('Líneas')
                    ->schema([
                        Repeater::make('lines')
                            ->relationship()
                            ->schema([
                                TextInput::make('description_original')
                                    ->label('Descripción')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.0001),
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('unit_id')
                                    ->label('Unidad')
                                    ->relationship('unit', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('unit_price')
                                    ->label('Precio unitario')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->minValue(0),
                            ])
                            ->defaultItems(1)
                            ->columns(3)
                            ->addActionLabel('Agregar línea')
                            ->reorderable(false)
                            ->required(),
                    ]),
            ]);
    }
}
