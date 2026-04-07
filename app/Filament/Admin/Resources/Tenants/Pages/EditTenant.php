<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Services\Subscriptions\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterSave(): void
    {
        /** @var Tenant $record */
        $record = $this->record;

        TenantResource::afterSave($record, $this->form->getRawState());
    }

    protected function getHeaderActions(): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->record;

        return [
            Action::make('forzar_cobro')
                ->label('Forzar cobro')
                ->color('warning')
                ->action(function () use ($tenant): void {
                    /** @var TenantSubscription|null $subscription */
                    $subscription = $tenant->subscriptions()->latest('created_at')->first();
                    if ($subscription === null) {
                        return;
                    }

                    app(SubscriptionService::class)->createRenewalPayment($subscription, 'cybersource');
                }),
            Action::make('suspender')
                ->label('Suspender')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($tenant): void {
                    $subscription = $tenant->subscriptions()->latest('created_at')->first();
                    if ($subscription !== null) {
                        $subscription->suspend();
                    }
                }),
            Action::make('reactivar')
                ->label('Reactivar')
                ->color('success')
                ->action(function () use ($tenant): void {
                    $subscription = $tenant->subscriptions()->latest('created_at')->first();
                    if ($subscription !== null) {
                        $subscription->update([
                            'status' => TenantSubscription::STATUS_ACTIVE,
                            'suspended_at' => null,
                            'grace_ends_at' => null,
                        ]);
                    }
                    $tenant->update(['is_active' => true]);
                }),
            DeleteAction::make(),
        ];
    }
}
