<?php

declare(strict_types=1);

use App\Models\Core\Payment;
use App\Models\Plan;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailedNotification;
use App\Notifications\SubscriptionReactivatedNotification;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Support\Facades\Notification;

test('create subscription stores active state and billing cycle', function (): void {
    $plan = Plan::factory()->create([
        'price_monthly' => 49.99,
        'price_yearly' => 499.99,
    ]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

    $subscription = app(SubscriptionService::class)->createSubscription(
        tenant: $tenant,
        plan: $plan,
        billingCycle: 'monthly',
        gateway: null,
    );

    expect($subscription->status)->toBe(TenantSubscription::STATUS_ACTIVE)
        ->and($subscription->billing_cycle)->toBe('monthly')
        ->and($subscription->next_billing_at)->not->toBeNull()
        ->and($tenant->fresh()->plan_id)->toBe($plan->id);
});

test('change plan applies immediate proration and updates period', function (): void {
    $oldPlan = Plan::factory()->create([
        'price_monthly' => 100,
        'price_yearly' => 1000,
    ]);
    $newPlan = Plan::factory()->create([
        'price_monthly' => 200,
        'price_yearly' => 1800,
    ]);
    $tenant = Tenant::factory()->create(['plan_id' => $oldPlan->id]);

    $service = app(SubscriptionService::class);
    $subscription = $service->createSubscription($tenant, $oldPlan, 'monthly', null);
    $subscription->update([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->addDays(20),
    ]);

    $updated = $service->changePlan(
        subscription: $subscription->fresh(),
        newPlan: $newPlan,
        newBillingCycle: 'yearly',
        prorate: true,
        gateway: null,
    );

    expect($updated->plan_id)->toBe($newPlan->id)
        ->and($updated->billing_cycle)->toBe('yearly')
        ->and($updated->status)->toBe(TenantSubscription::STATUS_ACTIVE)
        ->and($updated->next_billing_at)->not->toBeNull();
});

test('failed and completed payment update subscription status', function (): void {
    Notification::fake();

    $superAdmin = User::factory()->superAdmin()->create();
    $plan = Plan::factory()->create(['price_monthly' => 25]);
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
    $service = app(SubscriptionService::class);

    $subscription = $service->createSubscription($tenant, $plan, 'monthly', null);
    $payment = Payment::query()->create([
        'tenant_id' => $tenant->id,
        'paymentable_type' => TenantSubscription::class,
        'paymentable_id' => $subscription->id,
        'gateway' => 'cash',
        'amount' => 25,
        'currency' => 'USD',
        'reference_number' => Payment::generateReferenceNumber(),
        'status' => Payment::STATUS_PENDING,
    ]);

    $payment->markAsFailed('202', 'Error de prueba');
    $service->handlePaymentStatusFromGateway($payment->fresh());
    $subscription = $subscription->fresh();

    expect($subscription->status)->toBe(TenantSubscription::STATUS_PAST_DUE)
        ->and($subscription->grace_ends_at)->not->toBeNull()
        ->and($subscription->retry_count)->toBe(1)
        ->and($subscription->next_retry_at)->not->toBeNull();

    Notification::assertSentTo($superAdmin, SubscriptionPaymentFailedNotification::class);

    $payment->markAsCompleted();
    $service->handlePaymentStatusFromGateway($payment->fresh());
    $subscription = $subscription->fresh();

    expect($subscription->status)->toBe(TenantSubscription::STATUS_ACTIVE)
        ->and($subscription->payment_status)->toBe('paid')
        ->and($subscription->retry_count)->toBe(0)
        ->and((bool) $tenant->fresh()->is_active)->toBeTrue();

    Notification::assertSentTo($superAdmin, SubscriptionReactivatedNotification::class);
});
