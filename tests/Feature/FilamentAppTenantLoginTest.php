<?php

declare(strict_types=1);

use App\Filament\App\Pages\Auth\Login;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

afterEach(function (): void {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
});

test('filament app login rejects user belonging to another tenant', function () {
    $tenantA = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'ft-a']));
    $tenantB = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'ft-b']));

    User::factory()->create([
        'email' => 'other@tenant.test',
        'password' => 'password',
        'tenant_id' => $tenantB->id,
        'is_tenant_admin' => true,
        'is_super_admin' => false,
    ]);

    tenancy()->initialize($tenantA);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    Livewire::test(Login::class)
        ->set('data.email', 'other@tenant.test')
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['data.email']);
});
