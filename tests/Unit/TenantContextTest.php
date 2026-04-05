<?php

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Tests\TestCase;

uses(TestCase::class);

test('tenant context is registered as singleton', function () {
    expect(app(TenantContext::class))->toBe(app(TenantContext::class));
});

test('tenant context reflects tenant branch user and module helpers', function () {
    $ctx = app(TenantContext::class);

    expect($ctx->hasTenant())->toBeFalse()
        ->and($ctx->getTenantId())->toBeNull()
        ->and($ctx->getBranchId())->toBeNull()
        ->and($ctx->isMainBranch())->toBeFalse()
        ->and($ctx->isTenantAdmin())->toBeFalse();

    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->make(['id' => 'tenant-test-id']));
    $ctx->tenant = $tenant;

    expect($ctx->hasTenant())->toBeTrue()
        ->and($ctx->getTenantId())->toBe('tenant-test-id');

    $ctx->modules = ['inventory', 'packages'];
    expect($ctx->hasModule('inventory'))->toBeTrue()
        ->and($ctx->hasModule('printing'))->toBeFalse()
        ->and($ctx->hasAnyModule(['printing', 'packages']))->toBeTrue()
        ->and($ctx->hasAnyModule(['printing']))->toBeFalse();

    $branch = Branch::factory()->make([
        'tenant_id' => 'tenant-test-id',
        'is_main' => true,
    ]);
    $ctx->branch = $branch;
    expect($ctx->isMainBranch())->toBeTrue()
        ->and($ctx->getBranchId())->toBe($branch->id);

    $user = new User;
    $user->forceFill(['is_tenant_admin' => true]);
    $ctx->user = $user;
    expect($ctx->isTenantAdmin())->toBeTrue();
});
