<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;

afterEach(function (): void {
    Filament::setCurrentPanel(null);
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});

test('el administrador del tenant puede permisos Shield en panel app con tenancy', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'gate-tenant']));
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);

    tenancy()->initialize($tenant);
    Filament::setCurrentPanel(Filament::getPanel('app'));

    expect(Gate::forUser($user)->allows('ViewAny:Branch'))->toBeTrue();
});

test('el administrador del tenant no recibe bypass de isAdmin ni viewAdmin', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'gate-tenant-ad']));
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);

    tenancy()->initialize($tenant);
    Filament::setCurrentPanel(Filament::getPanel('app'));

    expect(Gate::forUser($user)->allows('isAdmin'))->toBeFalse()
        ->and(Gate::forUser($user)->allows('viewAdmin'))->toBeFalse();
});

test('un usuario de tenant sin flag admin no recibe permisos Shield por el gate', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'gate-tenant-user']));
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_tenant_admin' => false,
        'is_super_admin' => false,
    ]);

    tenancy()->initialize($tenant);
    Filament::setCurrentPanel(Filament::getPanel('app'));

    expect(Gate::forUser($user)->allows('ViewAny:Branch'))->toBeFalse();
});

test('el administrador del tenant puede permisos inventory.* sin panel Filament', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'gate-tenant-inv']));
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);

    tenancy()->initialize($tenant);

    expect(Gate::forUser($user)->allows('inventory.intake.create'))->toBeTrue();
});

test('el administrador del tenant no recibe bypass Shield sin tenancy aunque el panel sea app', function (): void {
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'gate-tenant-noten']));
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    expect(Gate::forUser($user)->allows('ViewAny:Branch'))->toBeFalse();
});
