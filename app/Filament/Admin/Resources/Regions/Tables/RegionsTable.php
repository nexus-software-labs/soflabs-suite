<?php

namespace App\Filament\Admin\Resources\Regions\Tables;

use App\Models\Branch;
use App\Models\Core\Region;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('region.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label(__('region.table.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('franchisee.name')
                    ->label(__('region.table.franchisee_local'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder(__('region.table_placeholders.unassigned')),

                TextColumn::make('country.region.franchisee.name')
                    ->label(__('region.table.franchisee_regional'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->description(fn (Region $record): string => $record->name ?? ''),

                TextColumn::make('countries_count')
                    ->label(__('region.table.countries_count'))
                    ->counts('countries')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('branches_count')
                    ->label(__('region.table.branches_count'))
                    ->getStateUsing(fn (Region $record): int => Branch::query()
                        ->withoutGlobalScopes()
                        ->whereHas('countryModel', fn ($q) => $q->where('region_id', $record->id))
                        ->count())
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label(__('region.table.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('region.table.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('region.table.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('franchisee_id')
                    ->label(__('region.filters.franchisee'))
                    ->relationship('franchisee', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label(__('region.filters.state'))
                    ->placeholder(__('region.filters.all'))
                    ->trueLabel(__('region.filters.active_only'))
                    ->falseLabel(__('region.filters.inactive_only')),
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
                ]),
            ])
            ->defaultSort('name');
    }
}
