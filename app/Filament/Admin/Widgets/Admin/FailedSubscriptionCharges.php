<?php

namespace App\Filament\Admin\Widgets\Admin;

use App\Models\Core\Payment;
use App\Models\Subscriptions\TenantSubscription;
use App\Services\Subscriptions\SubscriptionService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class FailedSubscriptionCharges extends TableWidget
{
    protected static ?string $heading = 'Cobros fallidos de suscripción';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Payment::query()
                ->where('paymentable_type', TenantSubscription::class)
                ->where('status', Payment::STATUS_FAILED)
                ->latest('created_at'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable(),
                TextColumn::make('gateway')
                    ->label('Pasarela')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD'),
                TextColumn::make('reason_message')
                    ->label('Motivo')
                    ->limit(60)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('gateway')
                    ->label('Pasarela')
                    ->options([
                        'cybersource' => 'CyberSource',
                        'transfer' => 'Transferencia',
                        'cash' => 'Efectivo',
                    ]),
            ])
            ->recordActions([
                Action::make('retry_now')
                    ->label('Reintentar ahora')
                    ->color('warning')
                    ->action(function (Payment $record): void {
                        /** @var TenantSubscription|null $subscription */
                        $subscription = TenantSubscription::query()->find($record->paymentable_id);
                        if ($subscription === null) {
                            return;
                        }

                        app(SubscriptionService::class)->createRenewalPayment(
                            subscription: $subscription,
                            gateway: $record->gateway,
                            metadata: [
                                'billing_type' => 'manual_retry',
                                'original_payment_id' => $record->id,
                            ],
                        );
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
