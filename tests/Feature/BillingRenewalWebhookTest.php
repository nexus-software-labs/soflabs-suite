<?php

declare(strict_types=1);

use App\Models\Core\Payment;
use App\Models\Plan;
use App\Models\SubscriptionAlert;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;

test('renewal webhook marks subscription as paid and logs alert', function (): void {
    config(['services.billing.webhook_secret' => 'test-secret']);

    $plan = Plan::factory()->create(['price_monthly' => 30]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id, 'is_active' => false]);
    $subscription = TenantSubscription::query()->create([
        'tenant_id' => $tenant->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenant->id,
        'plan_id' => $plan->id,
        'name' => 'principal',
        'slug' => 'main',
        'status' => TenantSubscription::STATUS_PAST_DUE,
        'billing_cycle' => 'monthly',
        'grace_ends_at' => now()->addDays(3),
    ]);

    $payment = Payment::query()->create([
        'tenant_id' => $tenant->id,
        'paymentable_type' => TenantSubscription::class,
        'paymentable_id' => $subscription->id,
        'gateway' => 'cash',
        'amount' => 30,
        'currency' => 'USD',
        'reference_number' => Payment::generateReferenceNumber(),
        'status' => Payment::STATUS_PENDING,
    ]);

    $response = $this->postJson(
        route('webhooks.billing.renewal', ['gateway' => 'cash']),
        [
            'payment_id' => $payment->id,
            'status' => Payment::STATUS_COMPLETED,
        ],
        [
            'X-Billing-Webhook-Secret' => 'test-secret',
        ],
    );

    $response->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(TenantSubscription::STATUS_ACTIVE)
        ->and((bool) $tenant->fresh()->is_active)->toBeTrue()
        ->and(SubscriptionAlert::query()->where('type', 'renewal_webhook_received')->exists())->toBeTrue();
});

test('renewal webhook rejects invalid secret', function (): void {
    config(['services.billing.webhook_secret' => 'test-secret']);

    $response = $this->postJson(
        route('webhooks.billing.renewal', ['gateway' => 'cash']),
        ['payment_id' => 999, 'status' => Payment::STATUS_COMPLETED],
        ['X-Billing-Webhook-Secret' => 'invalid'],
    );

    $response->assertUnauthorized();
});
