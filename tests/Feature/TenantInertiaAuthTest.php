<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

afterEach(function (): void {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});

/**
 * @return non-empty-string
 */
function tenantHost(string $subdomain): string
{
    return $subdomain.'.'.config('app.domain');
}

test('tenant panel login page renders with panel loginContext', function () {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'demo']);

    $response = $this->get('http://'.tenantHost('demo').'/panel/login');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/login')
        ->where('loginContext', 'panel')
    );
});

test('tenant inertia login page renders with tenant props', function () {
    $tenant = Tenant::factory()->create([
        'company_name' => 'Mi Empresa SL',
    ]);
    $tenant->domains()->create(['domain' => 'demo']);

    $response = $this->get('http://'.tenantHost('demo').'/login');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/login')
        ->where('loginContext', 'public')
        ->has('tenant', fn (Assert $t) => $t
            ->where('company_name', 'Mi Empresa SL')
            ->where('name', 'demo')
        )
    );
});

test('tenant login rejects invalid credentials', function () {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'demo']);

    User::factory()->create([
        'email' => 'u@test.com',
        'password' => 'password',
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->from('http://'.tenantHost('demo').'/login')->post('http://'.tenantHost('demo').'/login', [
        'email' => 'u@test.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('tenant login rejects user belonging to another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantA->domains()->create(['domain' => 'a']);
    $tenantB = Tenant::factory()->create();
    $tenantB->domains()->create(['domain' => 'b']);

    User::factory()->create([
        'email' => 'other@test.com',
        'password' => 'password',
        'tenant_id' => $tenantB->id,
    ]);

    $response = $this->from('http://'.tenantHost('a').'/login')->post('http://'.tenantHost('a').'/login', [
        'email' => 'other@test.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('tenant login succeeds and redirects to dashboard', function () {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'demo']);

    User::factory()->create([
        'email' => 'ok@test.com',
        'password' => 'password',
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->post('http://'.tenantHost('demo').'/login', [
        'email' => 'ok@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/panel/dashboard');
    $this->assertAuthenticated();
});

test('tenant logout ends session and redirects to login', function () {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'demo']);

    $user = User::factory()->create([
        'email' => 'out@test.com',
        'password' => 'password',
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->actingAs($user)->post('http://'.tenantHost('demo').'/panel/logout');

    $response->assertRedirect('/panel/login');
    $this->assertGuest();
});
