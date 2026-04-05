<?php

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\TenantContext;

beforeEach(function () {
    Event::fake();
});

test('tenant scope limits queries to the tenant in TenantContext', function () {
    $tenantA = Tenant::factory()->create(['id' => 'tenant-scope-a']);
    $tenantB = Tenant::factory()->create(['id' => 'tenant-scope-b']);

    Branch::factory()->create(['tenant_id' => $tenantA->id]);
    Branch::factory()->create(['tenant_id' => $tenantB->id]);

    $context = app(TenantContext::class);
    $context->tenant = $tenantA;

    expect(Branch::query()->count())->toBe(1)
        ->and(Branch::query()->value('tenant_id'))->toBe($tenantA->id);
});

test('tenant scope can be bypassed for queries using factoryWithoutTenantScope helper', function () {
    Tenant::factory()->create(['id' => 'tenant-scope-c']);
    Branch::factory()->create(['tenant_id' => 'tenant-scope-c']);

    $context = app(TenantContext::class);
    $context->tenant = Tenant::factory()->make(['id' => 'other-tenant']);

    $countWhileScoped = Branch::query()->count();

    $countUnscoped = Branch::factoryWithoutTenantScope(fn () => Branch::query()->count());

    expect($countWhileScoped)->toBe(0)
        ->and($countUnscoped)->toBe(1);
});

test('tenant module queries respect tenant context when set', function () {
    $tenant = Tenant::factory()->create(['id' => 'tenant-scope-d']);
    Tenant::factory()->create(['id' => 'another-tenant']);

    TenantModule::factory()->create([
        'tenant_id' => $tenant->id,
        'module' => 'inventory',
        'is_active' => true,
    ]);

    TenantModule::factory()->create([
        'tenant_id' => 'another-tenant',
        'module' => 'packages',
        'is_active' => true,
    ]);

    app(TenantContext::class)->tenant = $tenant;

    expect(TenantModule::query()->count())->toBe(1)
        ->and(TenantModule::query()->value('module'))->toBe('inventory');
});
