<?php

namespace App\Filament\Admin\Widgets\Admin;

use App\Models\SubscriptionAlert;
use App\Models\Subscriptions\TenantSubscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Suscripciones activas', (string) TenantSubscription::query()
                ->where('status', TenantSubscription::STATUS_ACTIVE)
                ->count())
                ->description('Tenants al día')
                ->color('success'),
            Stat::make('En gracia', (string) TenantSubscription::query()
                ->where('status', TenantSubscription::STATUS_PAST_DUE)
                ->count())
                ->description('Cobro fallido con gracia vigente')
                ->color('warning'),
            Stat::make('Suspendidas', (string) TenantSubscription::query()
                ->where('status', TenantSubscription::STATUS_SUSPENDED)
                ->count())
                ->description('Acceso bloqueado por mora')
                ->color('danger'),
            Stat::make('Alertas 24h', (string) SubscriptionAlert::query()
                ->where('created_at', '>=', now()->subDay())
                ->count())
                ->description('Eventos recientes de suscripción')
                ->color('info'),
        ];
    }
}
