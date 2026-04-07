<?php

namespace App\Filament\Admin\Resources\Promotions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('promotion.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label(__('promotion.table.description'))
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }

                        return $state;
                    }),

                BadgeColumn::make('discount_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'free_delivery' => 'Envío Gratis',
                        'percentage' => 'Porcentaje',
                        'fixed_amount' => 'Monto Fijo',
                        'fixed_rate' => 'Tarifa Fija',
                    })
                    ->colors([
                        'success' => 'free_delivery',
                        'warning' => 'percentage',
                        'primary' => 'fixed_amount',
                        'info' => 'fixed_rate',
                    ])
                    ->icons([
                        'heroicon-o-truck' => 'free_delivery',
                        'heroicon-o-receipt-percent' => 'percentage',
                        'heroicon-o-currency-dollar' => 'fixed_amount',
                        'heroicon-o-credit-card' => 'fixed_rate',
                    ]),

                TextColumn::make('discount_value')
                    ->label(__('promotion.table.value'))
                    ->formatStateUsing(
                        fn ($record) => $record->discount_type === 'free_delivery'
                            ? '-'
                            : ($record->discount_type === 'percentage'
                                ? $record->discount_value.'%'
                                : '$'.number_format($record->discount_value, 2))
                    )
                    ->alignCenter(),

                BadgeColumn::make('applies_to')
                    ->label('Aplica a')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'delivery' => 'Envío',
                        'subtotal' => 'Subtotal',
                        'weight' => 'Peso',
                    })
                    ->colors([
                        'info' => 'delivery',
                        'warning' => 'subtotal',
                        'primary' => 'weight',
                    ]),

                BadgeColumn::make('scope_type')
                    ->label(__('promotion.table.scope'))
                    ->formatStateUsing(fn (string $state): string => __("promotion.table_scope.{$state}"))
                    ->colors([
                        'success' => 'all',
                        'primary' => 'region',
                        'info' => 'country',
                        'warning' => 'branches',
                        'danger' => 'customers',
                    ]),

                TextColumn::make('customerTier.name')
                    ->label(__('promotion.table.category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->customerTier?->color ?? 'gray')
                    ->placeholder(__('promotion.placeholders.all'))
                    ->toggleable(),

                IconColumn::make('active')
                    ->label(__('promotion.table.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label(__('promotion.table.starts_at'))
                    ->dateTime('d/M/Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label(__('promotion.table.expires_at'))
                    ->dateTime('d/M/Y')
                    ->sortable()
                    ->color(
                        fn ($record) => $record->expires_at->isPast() ? 'danger' : 'success'
                    ),

                TextColumn::make('applications_count')
                    ->label(__('promotion.table.usages'))
                    ->counts('applications')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->label(__('promotion.filters.state'))
                    ->options([
                        true => __('promotion.filters.active'),
                        false => __('promotion.filters.inactive'),
                    ]),

                SelectFilter::make('discount_type')
                    ->label(__('promotion.filters.discount_type'))
                    ->options(__('promotion.discount_types')),

                SelectFilter::make('scope_type')
                    ->label(__('promotion.filters.scope'))
                    ->options(__('promotion.table_scope')),

                SelectFilter::make('customer_tier_id')
                    ->label(__('promotion.filters.customer_tier'))
                    ->relationship('customerTier', 'name', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('vigente')
                    ->label(__('promotion.filters.current'))
                    ->query(
                        fn (Builder $query): Builder => $query->where('starts_at', '<=', now())
                            ->where('expires_at', '>=', now())
                    ),

                Filter::make('expiradas')
                    ->label(__('promotion.filters.expired'))
                    ->query(
                        fn (Builder $query): Builder => $query->where('expires_at', '<', now())
                    ),
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
            ->defaultSort('created_at', 'desc');
    }
}
