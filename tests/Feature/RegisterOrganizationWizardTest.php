<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('panel_user', 'web');
    Role::findOrCreate('inventory_admin', 'web');
});

test('página de registro de organización renderiza con planes', function (): void {
    Plan::factory()->create(['is_active' => true, 'name' => 'Plan Demo']);

    $response = $this->get('http://'.config('app.domain').'/register-organization');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register-organization')
        ->has('plans', fn (Assert $p) => $p->has('0'))
        ->where('appDomain', config('app.domain')));
});

test('registro completo redirige al login del panel del tenant', function (): void {
    $plan = Plan::factory()->create([
        'is_active' => true,
        'price_monthly' => 10,
        'price_yearly' => 100,
        'modules' => ['inventory', 'packages', 'printing'],
    ]);

    $slug = 'wizard-'.str()->lower(fake()->lexify('??????????'));
    $email = 'wizard-'.str()->lower(fake()->lexify('??????')).'@example.com';

    $response = $this->post('http://'.config('app.domain').'/register-organization', [
        'id' => $slug,
        'company_name' => 'Wizard Co',
        'plan_id' => $plan->getKey(),
        'active_modules' => ['inventory'],
        'billing_cycle' => 'monthly',
        'billing_gateway' => 'cash',
        'db_mode' => 'shared',
        'admin_name' => 'Admin',
        'admin_email' => $email,
        'admin_password' => 'password12',
        'admin_password_confirmation' => 'password12',
    ]);

    $response->assertRedirect();

    expect(Tenant::query()->whereKey($slug)->exists())->toBeTrue()
        ->and(User::query()->where('email', $email)->exists())->toBeTrue();

    $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
    $expected = sprintf('%s://%s.%s/panel/login?registered=1', $scheme, $slug, config('app.domain'));
    expect($response->headers->get('Location'))->toBe($expected);
});

test('módulo fuera del plan es rechazado', function (): void {
    $plan = Plan::factory()->create([
        'is_active' => true,
        'modules' => ['inventory'],
    ]);

    $slug = 'bad-'.str()->lower(fake()->lexify('????????'));

    $response = $this->post('http://'.config('app.domain').'/register-organization', [
        'id' => $slug,
        'company_name' => 'X',
        'plan_id' => $plan->getKey(),
        'active_modules' => ['printing'],
        'billing_cycle' => 'monthly',
        'billing_gateway' => 'cash',
        'db_mode' => 'shared',
        'admin_name' => 'A',
        'admin_email' => 'bad-mod-'.str()->lower(fake()->lexify('??????')).'@example.com',
        'admin_password' => 'password12',
        'admin_password_confirmation' => 'password12',
    ]);

    $response->assertSessionHasErrors('active_modules');
    expect(Tenant::query()->whereKey($slug)->exists())->toBeFalse();
});
