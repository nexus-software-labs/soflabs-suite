<?php

declare(strict_types=1);

use App\Events\TenantProvisioned;
use App\Filament\Admin\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Admin\Resources\Tenants\Pages\EditTenant;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Event::fake();
    Role::findOrCreate('panel_user', 'web');
    Role::findOrCreate('inventory_admin', 'web');
});

test('crear inquilino desde Filament sincroniza tenant_modules', function (): void {
    $plan = Plan::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'id' => 'filament-tenant-a',
            'company_name' => 'Empresa Prueba',
            'plan_id' => $plan->getKey(),
            'db_mode' => 'shared',
            'is_active' => true,
            'billing_gateway' => 'cash',
            'active_modules' => ['inventory', 'printing'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenant = Tenant::query()->find('filament-tenant-a');

    expect($tenant)->not->toBeNull()
        ->and($tenant->company_name)->toBe('Empresa Prueba');

    expect((bool) TenantModule::query()->where('tenant_id', 'filament-tenant-a')->where('module', 'inventory')->value('is_active'))->toBeTrue()
        ->and((bool) TenantModule::query()->where('tenant_id', 'filament-tenant-a')->where('module', 'packages')->value('is_active'))->toBeFalse()
        ->and((bool) TenantModule::query()->where('tenant_id', 'filament-tenant-a')->where('module', 'printing')->value('is_active'))->toBeTrue();

    expect($tenant->domains()->count())->toBe(1)
        ->and(Branch::query()->where('tenant_id', 'filament-tenant-a')->where('code', 'MAIN')->where('is_main', true)->exists())->toBeTrue();

    Event::assertDispatched(TenantProvisioned::class);
});

test('editar inquilino desde Filament actualiza tenant_modules', function (): void {
    $plan = Plan::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create([
        'id' => 'filament-tenant-b',
        'plan_id' => $plan->id,
    ]));

    TenantModule::query()->create([
        'tenant_id' => $tenant->id,
        'module' => 'inventory',
        'is_active' => true,
        'activated_at' => now(),
    ]);

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(EditTenant::class, ['record' => $tenant->id])
        ->fillForm([
            'company_name' => $tenant->company_name ?? 'Empresa',
            'plan_id' => $tenant->plan_id,
            'db_mode' => $tenant->db_mode,
            'is_active' => $tenant->is_active,
            'trial_ends_at' => $tenant->trial_ends_at,
            'subscribed_at' => $tenant->subscribed_at,
            'phone' => $tenant->phone,
            'country' => $tenant->country,
            'active_modules' => ['packages'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect((bool) TenantModule::query()->where('tenant_id', $tenant->id)->where('module', 'inventory')->value('is_active'))->toBeFalse()
        ->and((bool) TenantModule::query()->where('tenant_id', $tenant->id)->where('module', 'packages')->value('is_active'))->toBeTrue();
});

test('crear inquilino con administrador inicial crea usuario tenant admin con roles', function (): void {
    $plan = Plan::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'id' => 'filament-tenant-admin',
            'company_name' => 'Empresa Con Admin',
            'plan_id' => $plan->getKey(),
            'db_mode' => 'shared',
            'is_active' => true,
            'billing_gateway' => 'cash',
            'active_modules' => ['inventory'],
            'admin_name' => 'Admin Inicial',
            'admin_email' => 'tenant-admin@example.com',
            'admin_password' => 'password12',
            'admin_password_confirmation' => 'password12',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $tenantUser = User::query()->where('email', 'tenant-admin@example.com')->first();

    expect($tenantUser)->not->toBeNull()
        ->and($tenantUser->tenant_id)->toBe('filament-tenant-admin')
        ->and($tenantUser->is_tenant_admin)->toBeTrue()
        ->and($tenantUser->hasRole('panel_user'))->toBeTrue()
        ->and($tenantUser->hasRole('inventory_admin'))->toBeTrue();

    expect($tenantUser->branch_id)->toBe(
        Branch::query()->where('tenant_id', 'filament-tenant-admin')->where('is_main', true)->value('id')
    );
});
