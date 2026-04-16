<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Core\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Onboarding\RegisterOrganizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Event::fake();
    Role::findOrCreate('panel_user', 'web');
    Role::findOrCreate('inventory_admin', 'web');
});

test('register crea tenant provisionado y usuario administrador', function (): void {
    $plan = Plan::factory()->create([
        'price_monthly' => 49,
        'price_yearly' => 490,
    ]);

    $service = app(RegisterOrganizationService::class);

    $tenant = $service->register([
        'id' => 'org-self-serve',
        'company_name' => 'Self Serve SA',
        'plan_id' => $plan->getKey(),
        'db_mode' => 'shared',
        'is_active' => true,
        'active_modules' => ['inventory'],
        'billing_cycle' => 'monthly',
        'billing_gateway' => 'cash',
        'admin_name' => 'Admin Org',
        'admin_email' => 'admin-org@example.com',
        'admin_password' => 'password12',
        'admin_password_confirmation' => 'password12',
    ]);

    expect($tenant->id)->toBe('org-self-serve')
        ->and($tenant->company_name)->toBe('Self Serve SA');

    expect(Tenant::query()->whereKey('org-self-serve')->exists())->toBeTrue()
        ->and(Branch::query()->where('tenant_id', 'org-self-serve')->where('is_main', true)->exists())->toBeTrue();

    $user = User::query()->where('email', 'admin-org@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe('org-self-serve')
        ->and($user->is_tenant_admin)->toBeTrue()
        ->and($user->hasRole('panel_user'))->toBeTrue()
        ->and($user->hasRole('inventory_admin'))->toBeTrue();
});

test('register rechaza subdominio duplicado', function (): void {
    $plan = Plan::factory()->create();

    Tenant::withoutEvents(fn () => Tenant::factory()->create([
        'id' => 'dup-tenant',
        'plan_id' => $plan->getKey(),
    ]));

    $service = app(RegisterOrganizationService::class);

    $service->register([
        'id' => 'dup-tenant',
        'company_name' => 'Otra',
        'plan_id' => $plan->getKey(),
        'active_modules' => [],
        'billing_gateway' => 'cash',
        'admin_name' => 'A',
        'admin_email' => 'a@example.com',
        'admin_password' => 'password12',
        'admin_password_confirmation' => 'password12',
    ]);
})->throws(ValidationException::class);

test('plan gratuito no inicia cobro con pasarela', function (): void {
    $plan = Plan::factory()->create([
        'price_monthly' => 0,
        'price_yearly' => 0,
    ]);

    $service = app(RegisterOrganizationService::class);

    $tenant = $service->register([
        'id' => 'free-org',
        'company_name' => 'Gratis SL',
        'plan_id' => $plan->getKey(),
        'active_modules' => [],
        'billing_cycle' => 'monthly',
        'billing_gateway' => 'cash',
        'admin_name' => 'Free Admin',
        'admin_email' => 'free@example.com',
        'admin_password' => 'password12',
        'admin_password_confirmation' => 'password12',
    ]);

    expect($tenant->subscriptions()->count())->toBe(1);

    $subscription = $tenant->subscriptions()->first();
    expect($subscription)->not->toBeNull()
        ->and(Payment::query()->where('paymentable_id', $subscription->id)->count())->toBe(0);
});
