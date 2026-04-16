<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Movements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['product', 'warehouse', 'performer']),
            )
            ->columns([
                TextColumn::make('moved_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => ['inbound', 'adjustment_increase'],
                        'danger' => ['outbound', 'adjustment_decrease'],
                    ]),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('stock_after')
                    ->label('Stock final')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label('Referencia')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->options([
                        'inbound' => 'Entrada',
                        'outbound' => 'Salida',
                        'adjustment_increase' => 'Ajuste positivo',
                        'adjustment_decrease' => 'Ajuste negativo',
                    ]),
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Bodega')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Producto')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('reference_type')
                    ->label('Tipo referencia')
                    ->options([
                        'intake_document' => 'Documento de entrada',
                        'outbound_request' => 'Solicitud de salida',
                        'inventory_adjustment' => 'Ajuste de inventario',
                    ]),
                Filter::make('moved_at')
                    ->label('Fecha movimiento')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde'),
                        DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('moved_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('moved_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => false),
                ]),
            ])
            ->defaultSort('moved_at', 'desc');
    }
}
