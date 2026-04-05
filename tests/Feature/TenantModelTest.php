<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Support\Facades\Event;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
});

test('tenant hasModule returns true when module is active', function () {
    $tenant = Tenant::factory()->create();

    TenantModule::factory()->create([
        'tenant_id' => $tenant->id,
        'module' => 'inventory',
        'is_active' => true,
    ]);

    expect($tenant->fresh()->hasModule('inventory'))->toBeTrue();
});

test('tenant hasModule returns false when module is inactive or missing', function () {
    $tenant = Tenant::factory()->create();

    TenantModule::factory()->create([
        'tenant_id' => $tenant->id,
        'module' => 'packages',
        'is_active' => false,
    ]);

    expect($tenant->fresh()->hasModule('packages'))->toBeFalse()
        ->and($tenant->fresh()->hasModule('inventory'))->toBeFalse();
});

test('tenant plan relationship resolves the related plan', function () {
    $plan = Plan::factory()->create();
    $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

    expect($tenant->plan)->toBeInstanceOf(Plan::class)
        ->and($tenant->plan->is($plan))->toBeTrue();
});

test('tenant getCustomColumns lists persisted attribute columns', function () {
    expect(Tenant::getCustomColumns())->toContain(
        'company_name',
        'plan_id',
        'db_mode',
        'is_active',
        'trial_ends_at',
        'subscribed_at',
    )->and(Tenant::getCustomColumns())->toContain('id');
});
