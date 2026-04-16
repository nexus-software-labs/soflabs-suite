<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Adjustments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ajuste de inventario')
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
                        Select::make('product_id')
                            ->label('Producto')
                            ->relationship(
                                name: 'product',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('adjustment_type')
                            ->label('Tipo de ajuste')
                            ->options([
                                'positive' => 'Positivo',
                                'negative' => 'Negativo',
                            ])
                            ->required(),
                        TextInput::make('difference_quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0.0001)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
