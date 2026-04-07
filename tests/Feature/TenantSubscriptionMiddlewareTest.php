<?php

declare(strict_types=1);

use App\Http\Middleware\CheckTenantSubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('middleware blocks suspended tenant subscription', function (): void {
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
    $subscription = TenantSubscription::query()->create([
        'tenant_id' => $tenant->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenant->id,
        'plan_id' => $plan->id,
        'name' => 'principal',
        'slug' => 'main',
        'status' => TenantSubscription::STATUS_SUSPENDED,
        'billing_cycle' => 'monthly',
    ]);

    tenancy()->initialize($tenant);
    app(TenantContext::class)->subscription = $subscription;

    $request = Request::create('/tenant/dashboard', 'GET');
    app(CheckTenantSubscriptionStatus::class)->handle($request, function () {
        return response('ok');
    });
})->throws(HttpException::class);

afterEach(function (): void {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});
