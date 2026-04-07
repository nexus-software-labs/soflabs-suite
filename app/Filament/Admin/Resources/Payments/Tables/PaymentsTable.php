<?php

namespace App\Filament\Admin\Resources\Payments\Tables;

use App\Models\Core\Payment;
use App\Models\PreAlertOrder;
use App\Models\PrintOrder;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label(__('payment.table.reference'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('paymentable_type')
                    ->label(__('payment.table.type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PreAlertOrder::class => __('payment.paymentable_types.pre_alert'),
                        PrintOrder::class => __('payment.paymentable_types.print_order'),
                        default => class_basename($state),
                    }),

                TextColumn::make('gateway')
                    ->label(__('payment.table.method'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Arr::get(__('payment.gateways'), $state, $state))
                    ->color(fn (string $state): string => match ($state) {
                        'cybersource' => 'info',
                        'transfer' => 'warning',
                        'cash' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label(__('payment.table.amount'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('payment.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (Payment $record): string => (string) __('payment.statuses.'.$record->status))
                    ->color(fn (Payment $record): string => $record->status_color),

                TextColumn::make('customer_name')
                    ->label(__('payment.table.customer'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('payment.table.date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gateway')
                    ->label(__('payment.filters.method'))
                    ->options([
                        'cybersource' => __('payment.gateways.cybersource'),
                        'transfer' => __('payment.gateways.transfer'),
                        'cash' => __('payment.gateways.cash'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('payment.filters.status'))
                    ->options([
                        Payment::STATUS_PENDING => __('payment.statuses.pending'),
                        Payment::STATUS_PROCESSING => __('payment.statuses.processing'),
                        Payment::STATUS_COMPLETED => __('payment.statuses.completed'),
                        Payment::STATUS_FAILED => __('payment.statuses.failed'),
                        Payment::STATUS_CANCELLED => __('payment.statuses.cancelled'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
