<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Stocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['product', 'warehouse']),
            )
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Existencia')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('reserved_quantity')
                    ->label('Reservado')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
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
                Filter::make('has_reserved')
                    ->label('Con reservado')
                    ->query(fn (Builder $query): Builder => $query->where('reserved_quantity', '>', 0)),
                Filter::make('out_of_stock')
                    ->label('Sin stock')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<=', 0)),
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
            ->defaultSort('updated_at', 'desc');
    }
}
