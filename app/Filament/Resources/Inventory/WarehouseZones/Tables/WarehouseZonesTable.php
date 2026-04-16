<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\WarehouseZones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['warehouse']),
            )
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Zona')
                    ->searchable(),
                TextColumn::make('storage_condition')
                    ->label('Condición')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
