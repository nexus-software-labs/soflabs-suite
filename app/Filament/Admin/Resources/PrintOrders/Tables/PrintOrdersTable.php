<?php

namespace App\Filament\Admin\Resources\PrintOrders\Tables;

use App\Models\Printing\PrintOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PrintOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('print_order.table.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                TextColumn::make('customer_name')
                    ->label(__('print_order.table.customer_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_email')
                    ->label(__('print_order.table.customer_email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_phone')
                    ->label(__('print_order.table.customer_phone'))
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label(__('print_order.table.branch'))
                    ->searchable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('print_order.table.status'))
                    ->formatStateUsing(fn (string $state): string => __('print_order.status.'.$state) ?: $state)
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => ['ready', 'delivered'],
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                TextColumn::make('pages_count')
                    ->label(__('print_order.table.pages_count'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' '.__('print_order.table.pages_suffix')),

                TextColumn::make('copies')
                    ->label(__('print_order.table.copies'))
                    ->numeric()
                    ->sortable(),

                BadgeColumn::make('print_type')
                    ->label(__('print_order.table.print_type'))
                    ->formatStateUsing(fn (string $state): string => __('print_order.print_type.'.$state))
                    ->colors([
                        'secondary' => 'bw',
                        'primary' => 'color',
                    ]),

                TextColumn::make('delivery_method')
                    ->label(__('print_order.table.delivery_method'))
                    ->formatStateUsing(fn (string $state): string => __('print_order.delivery_method.'.$state))
                    ->badge()
                    ->icon(fn (string $state): string => $state === 'pickup' ? 'heroicon-o-building-storefront' : 'heroicon-o-truck'),

                TextColumn::make('pickupLocation.name')
                    ->label(__('print_order.table.pickup_location'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total')
                    ->label(__('print_order.table.total'))
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                BadgeColumn::make('payment_status')
                    ->label(__('print_order.table.payment_status'))
                    ->formatStateUsing(fn (string $state): string => __('print_order.payment_status.'.$state) ?: $state)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),

                TextColumn::make('created_at')
                    ->label(__('print_order.table.created_at'))
                    ->dateTime('d/M/Y h:i A')
                    ->sortable(),

                IconColumn::make('downloaded_at')
                    ->label(__('print_order.table.downloaded_at'))
                    ->icon(
                        fn (?PrintOrder $record): ?string => $record && $record->downloaded_at
                            ? 'heroicon-o-check-circle'
                            : null
                    )
                    ->color('success')
                    ->tooltip(
                        fn (?PrintOrder $record): ?string => $record && $record->downloaded_at
                            ? __('print_order.tooltips.downloaded_at', ['date' => $record->downloaded_at->format('d/M/Y h:i A')])
                            : __('print_order.tooltips.not_downloaded')
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('print_order.filters.status'))
                    ->options([
                        'pending' => __('print_order.status.pending'),
                        'printing' => __('print_order.status.printing'),
                        'ready' => __('print_order.status.ready'),
                        'delivered' => __('print_order.status.delivered'),
                        'cancelled' => __('print_order.status.cancelled'),
                    ]),

                SelectFilter::make('payment_status')
                    ->label(__('print_order.filters.payment_status'))
                    ->options([
                        'pending' => __('print_order.payment_status.pending'),
                        'paid' => __('print_order.payment_status.paid'),
                        'failed' => __('print_order.payment_status.failed'),
                    ]),

                SelectFilter::make('delivery_method')
                    ->label(__('print_order.filters.delivery_method'))
                    ->options([
                        'pickup' => __('print_order.filters.pickup'),
                        'delivery' => __('print_order.filters.delivery'),
                    ]),

                SelectFilter::make('print_type')
                    ->label(__('print_order.filters.print_type'))
                    ->options([
                        'bw' => __('print_order.filters.bw'),
                        'color' => __('print_order.print_type.color'),
                    ]),
            ])
            ->recordActions([
                Action::make('mark_processing')
                    ->label(__('print_order.actions.mark_processing'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (PrintOrder $record) => $record->status === 'pending')
                    ->action(function (PrintOrder $record) {
                        $record->updateStatus('printing', __('print_order.status_comments.printing'));
                        Notification::make()
                            ->title(__('print_order.notifications.status_updated'))
                            ->success()
                            ->send();
                    }),

                Action::make('mark_ready')
                    ->label(__('print_order.actions.mark_ready'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PrintOrder $record) => $record->status === 'printing')
                    ->action(function (PrintOrder $record) {
                        $record->updateStatus('ready', __('print_order.status_comments.ready'));
                        Notification::make()
                            ->title(__('print_order.notifications.order_ready'))
                            ->success()
                            ->send();
                    }),

                Action::make('mark_delivered')
                    ->label(__('print_order.actions.mark_delivered'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PrintOrder $record) => $record->status === 'ready')
                    ->action(function (PrintOrder $record) {
                        $record->updateStatus('delivered', __('print_order.status_comments.delivered'));
                        Notification::make()
                            ->title(__('print_order.notifications.order_delivered'))
                            ->success()
                            ->send();
                    }),

                Action::make('download_file')
                    ->label(__('print_order.actions.download_file'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(
                        fn (PrintOrder $record): ?string => $record->getFirstMedia('print-files')
                            ? route('print-orders.download', $record->id)
                            : null
                    )
                    ->openUrlInNewTab(false)
                    ->visible(fn (PrintOrder $record) => $record->getFirstMedia('print-files') !== null)
                    ->action(function (PrintOrder $record) {
                        // Actualizar downloaded_at cuando se descarga
                        if (! $record->downloaded_at) {
                            $record->update(['downloaded_at' => now()]);
                        }
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
