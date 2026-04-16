<?php

namespace App\Filament\Admin\Resources\CustomerTiers\Tables;

use App\Models\Core\CustomerTier;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomerTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customer_tier.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (CustomerTier $record): string => $record->description ?? ''),

                TextColumn::make('slug')
                    ->label(__('customer_tier.table.slug'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('priority')
                    ->label(__('customer_tier.table.priority'))
                    ->sortable()
                    ->badge()
                    ->color(fn (CustomerTier $record): string => match (true) {
                        $record->priority >= 75 => 'success',
                        $record->priority >= 50 => 'warning',
                        default => 'gray'
                    }
                    ),

                TextColumn::make('customers_count')
                    ->label(__('customer_tier.table.customers_count'))
                    ->counts('customers')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('benefits_count')
                    ->label(__('customer_tier.table.benefits_count'))
                    ->counts('benefits')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label(__('customer_tier.table.state'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('customer_tier.table.created'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('customer_tier.table.updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('is_active')
                    ->label(__('customer_tier.filters.state'))
                    ->placeholder(__('customer_tier.filters.all'))
                    ->trueLabel(__('customer_tier.filters.active'))
                    ->falseLabel(__('customer_tier.filters.inactive')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),

                    BulkAction::make('activate')
                        ->label(__('customer_tier.actions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label(__('customer_tier.actions.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
