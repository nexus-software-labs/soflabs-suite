<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    Event::fake();
});

test('crear usuario desde Filament', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Usuario Nuevo',
            'email' => 'nuevo-usuario@test.com',
            'password' => 'contraseña-segura-1',
            'tenant_id' => null,
            'branch_id' => null,
            'is_tenant_admin' => false,
            'is_super_admin' => false,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'nuevo-usuario@test.com')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('contraseña-segura-1', $user->password))->toBeTrue()
        ->and($user->is_active)->toBeTrue();
});

test('editar usuario sin cambiar contraseña conserva el hash', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'usr-res-tenant']));

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'email' => 'miembro@test.com',
        'password' => Hash::make('hash-original'),
    ]);

    $hashAntes = $user->fresh()->password;

    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'tenant_id' => $tenant->id,
            'branch_id' => null,
            'is_tenant_admin' => true,
            'is_super_admin' => false,
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->password)->toBe($hashAntes)
        ->and($user->fresh()->is_tenant_admin)->toBeTrue();
});
