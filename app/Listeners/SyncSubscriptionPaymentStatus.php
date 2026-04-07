<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Services\Subscriptions\SubscriptionService;

class SyncSubscriptionPaymentStatus
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(PaymentCompleted|PaymentFailed $event): void
    {
        $this->subscriptionService->handlePaymentStatusFromGateway($event->payment);
    }
}
