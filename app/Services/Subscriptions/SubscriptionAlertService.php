<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\SubscriptionAlert;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailedNotification;
use App\Notifications\SubscriptionReactivatedNotification;
use App\Notifications\SubscriptionSuspendedNotification;
use Illuminate\Support\Facades\Log;

class SubscriptionAlertService
{
    public function notify(
        string $type,
        string $title,
        ?string $message = null,
        ?TenantSubscription $subscription = null,
        string $level = 'info',
        array $context = [],
    ): SubscriptionAlert {
        $alert = SubscriptionAlert::query()->create([
            'tenant_id' => $subscription?->tenant_id,
            'subscription_id' => $subscription?->id,
            'type' => $type,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'context' => $context,
            'notified_at' => now(),
        ]);

        Log::info('[subscription-alert] '.$title, [
            'type' => $type,
            'tenant_id' => $subscription?->tenant_id,
            'subscription_id' => $subscription?->id,
            'level' => $level,
            'context' => $context,
        ]);

        if ($subscription !== null) {
            $this->sendAdminNotification($type, $subscription);
        }

        return $alert;
    }

    private function sendAdminNotification(string $type, TenantSubscription $subscription): void
    {
        $admins = User::query()
            ->where('is_super_admin', true)
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            if ($type === 'subscription_payment_failed') {
                $admin->notify(new SubscriptionPaymentFailedNotification($subscription));
            }

            if ($type === 'subscription_suspended') {
                $admin->notify(new SubscriptionSuspendedNotification($subscription));
            }

            if ($type === 'subscription_payment_completed') {
                $admin->notify(new SubscriptionReactivatedNotification($subscription));
            }
        }
    }
}
