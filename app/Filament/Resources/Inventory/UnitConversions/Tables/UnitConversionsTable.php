<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\UnitConversions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitConversionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['fromUnit', 'toUnit']),
            )
            ->columns([
                TextColumn::make('fromUnit.abbreviation')
                    ->label('Desde')
                    ->searchable(),
                TextColumn::make('toUnit.abbreviation')
                    ->label('Hacia')
                    ->searchable(),
                TextColumn::make('factor')
                    ->label('Factor')
                    ->numeric(decimalPlaces: 8),
            ])
            ->filters([
                //
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
