<?php

declare(strict_types=1);

use App\Filament\App\Plugins\TenantModulePluginsRegistration;
use App\Models\User;
use App\Modules\Inventory\Filament\InventoryPlugin;
use App\Modules\Printing\Filament\PrintingPlugin;
use App\Services\TenantContext;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

test('isAdmin gate allows only platform super admins', function () {
    $super = User::factory()->superAdmin()->create();
    $tenantAdmin = User::factory()->create([
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);
    $neither = User::factory()->create([
        'is_tenant_admin' => false,
        'is_super_admin' => false,
    ]);

    expect(Gate::forUser($super)->allows('isAdmin'))->toBeTrue();
    expect(Gate::forUser($tenantAdmin)->allows('isAdmin'))->toBeFalse();
    expect(Gate::forUser($neither)->allows('isAdmin'))->toBeFalse();
});

test('viewAdmin gate allows only users with is_super_admin', function () {
    $super = User::factory()->superAdmin()->create();
    $superTenantAdmin = User::factory()->create([
        'is_tenant_admin' => true,
        'is_super_admin' => true,
        'tenant_id' => null,
    ]);
    $regular = User::factory()->create([
        'is_super_admin' => false,
    ]);

    expect(Gate::forUser($super)->allows('viewAdmin'))->toBeTrue()
        ->and(Gate::forUser($superTenantAdmin)->allows('viewAdmin'))->toBeTrue()
        ->and(Gate::forUser($regular)->allows('viewAdmin'))->toBeFalse();
});

test('FilamentUser allows any authenticated user for admin panel gate at model level', function () {
    $regular = User::factory()->create(['is_super_admin' => false]);
    $panel = Panel::make()->id('admin');

    expect($regular->canAccessPanel($panel))->toBeTrue();
});

test('tenant module plugins registration maps active modules', function () {
    $ctx = app(TenantContext::class);
    $ctx->modules = ['inventory', 'printing', 'unknown'];

    $plugins = TenantModulePluginsRegistration::pluginsForContext($ctx);

    expect($plugins)->toHaveCount(2);
    expect($plugins[0])->toBeInstanceOf(InventoryPlugin::class);
    expect($plugins[1])->toBeInstanceOf(PrintingPlugin::class);
});
