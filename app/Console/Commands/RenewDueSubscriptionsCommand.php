<?php

namespace App\Console\Commands;

use App\Services\Subscriptions\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:renew-due {--gateway=cybersource}')]
#[Description('Procesa renovaciones pendientes de suscripciones de tenants')]
class RenewDueSubscriptionsCommand extends Command
{
    public function handle(SubscriptionService $subscriptionService): int
    {
        $gateway = (string) $this->option('gateway');
        $processed = $subscriptionService->processDueRenewals(
            asOf: Carbon::now(),
            gateway: $gateway,
        );

        $this->info("Renovaciones procesadas: {$processed}");

        return self::SUCCESS;
    }
}
