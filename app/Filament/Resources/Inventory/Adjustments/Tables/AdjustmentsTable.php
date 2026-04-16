<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Adjustments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['product', 'warehouse', 'performer']),
            )
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => 'positive',
                        'danger' => 'negative',
                    ]),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable(),
                TextColumn::make('difference_quantity')
                    ->label('Diferencia')
                    ->numeric(decimalPlaces: 4),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->reason),
            ])
            ->filters([
                SelectFilter::make('adjustment_type')
                    ->options([
                        'positive' => 'Positivo',
                        'negative' => 'Negativo',
                    ]),
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
            ->defaultSort('adjusted_at', 'desc');
    }
}
