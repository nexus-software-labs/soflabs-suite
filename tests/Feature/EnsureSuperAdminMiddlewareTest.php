<?php

declare(strict_types=1);

use App\Models\User;

test('non super admin is redirected from filament admin with flash message', function () {
    $user = User::factory()->create([
        'is_super_admin' => false,
        'is_tenant_admin' => true,
        'tenant_id' => null,
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'))
        ->assertSessionHas(
            'error',
            'El panel landlord solo admite cuentas con rol de super administrador (is_super_admin).',
        );
});

test('super admin can access filament admin dashboard', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertOk();
});
