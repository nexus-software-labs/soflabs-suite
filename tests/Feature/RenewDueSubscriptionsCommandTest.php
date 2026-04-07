<?php

declare(strict_types=1);

use App\Models\Core\Payment;
use App\Models\Plan;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\SubscriptionSuspendedNotification;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Support\Facades\Notification;

test('renew due command creates renewal payment', function (): void {
    $plan = Plan::factory()->create(['price_monthly' => 19.99]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

    $subscription = app(SubscriptionService::class)->createSubscription($tenant, $plan, 'monthly', null);
    $subscription->update([
        'status' => TenantSubscription::STATUS_ACTIVE,
        'next_billing_at' => now()->subMinute(),
    ]);

    $this->artisan('subscriptions:renew-due --gateway=cash')
        ->assertSuccessful();

    expect(
        Payment::query()
            ->where('paymentable_type', TenantSubscription::class)
            ->where('paymentable_id', $subscription->id)
            ->exists()
    )->toBeTrue();
});

test('renew due command retries past due subscription when next_retry_at is due', function (): void {
    $plan = Plan::factory()->create(['price_monthly' => 19.99]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

    $subscription = app(SubscriptionService::class)->createSubscription($tenant, $plan, 'monthly', null);
    $subscription->update([
        'status' => TenantSubscription::STATUS_PAST_DUE,
        'grace_ends_at' => now()->addDays(5),
        'next_retry_at' => now()->subMinute(),
        'next_billing_at' => now()->addDay(),
    ]);

    $this->artisan('subscriptions:renew-due --gateway=cash')
        ->assertSuccessful();

    expect(
        Payment::query()
            ->where('paymentable_type', TenantSubscription::class)
            ->where('paymentable_id', $subscription->id)
            ->where('metadata->billing_type', 'subscription_retry')
            ->exists()
    )->toBeTrue();
});

test('renew due command suspends past due subscriptions after grace', function (): void {
    Notification::fake();
    $superAdmin = User::factory()->superAdmin()->create();

    $plan = Plan::factory()->create(['price_monthly' => 19.99]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id, 'is_active' => true]);

    $subscription = app(SubscriptionService::class)->createSubscription($tenant, $plan, 'monthly', null);
    $subscription->update([
        'status' => TenantSubscription::STATUS_PAST_DUE,
        'grace_ends_at' => now()->subDay(),
        'next_billing_at' => now()->addDay(),
    ]);

    $this->artisan('subscriptions:renew-due --gateway=cash')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(TenantSubscription::STATUS_SUSPENDED)
        ->and((bool) $tenant->fresh()->is_active)->toBeFalse();

    Notification::assertSentTo($superAdmin, SubscriptionSuspendedNotification::class);
});
