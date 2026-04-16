<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto')
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        Select::make('group_id')
                            ->label('Grupo')
                            ->relationship(
                                name: 'group',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('brand_id')
                            ->label('Marca')
                            ->relationship(
                                name: 'brand',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload(),
                        Select::make('purchase_unit_id')
                            ->label('Unidad compra')
                            ->relationship('purchaseUnit', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('stock_unit_id')
                            ->label('Unidad stock')
                            ->relationship('stockUnit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('sales_unit_id')
                            ->label('Unidad venta')
                            ->relationship('salesUnit', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('valuation_method')
                            ->label('Método valoración')
                            ->options([
                                'fifo' => 'FIFO',
                                'weighted_average' => 'Promedio ponderado',
                            ])
                            ->default('fifo')
                            ->required(),
                        TextInput::make('minimum_stock')
                            ->label('Stock mínimo')
                            ->numeric()
                            ->minValue(0),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
