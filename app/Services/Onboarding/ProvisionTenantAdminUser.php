<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Crea el primer usuario administrador del tenant tras el aprovisionamiento (sucursal principal).
 */
final class ProvisionTenantAdminUser
{
    /**
     * @param  array<string, mixed>  $rawFormState  Debe incluir admin_name, admin_email, admin_password y active_modules (array).
     */
    public function provision(Tenant $tenant, array $rawFormState): ?User
    {
        $email = trim((string) ($rawFormState['admin_email'] ?? ''));
        if ($email === '') {
            return null;
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'admin_email' => __('Ya existe un usuario con este correo.'),
            ]);
        }

        $name = trim((string) ($rawFormState['admin_name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'admin_name' => __('El nombre del administrador es obligatorio.'),
            ]);
        }

        $password = (string) ($rawFormState['admin_password'] ?? '');
        if ($password === '') {
            throw ValidationException::withMessages([
                'admin_password' => __('La contraseña del administrador es obligatoria.'),
            ]);
        }

        $branch = Branch::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('is_main', true)
            ->first();

        if ($branch === null) {
            throw ValidationException::withMessages([
                'admin_email' => __('No se encontró la sucursal principal del inquilino.'),
            ]);
        }

        $selected = $rawFormState['active_modules'] ?? [];
        $selected = is_array($selected) ? $selected : [];

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'tenant_id' => $tenant->getKey(),
            'branch_id' => $branch->getKey(),
            'is_tenant_admin' => true,
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $this->assignDefaultRoles($user, $selected);

        return $user->fresh();
    }

    /**
     * @param  array<int, string>  $activeModules
     */
    private function assignDefaultRoles(User $user, array $activeModules): void
    {
        $panelUser = Role::query()->where('name', 'panel_user')->where('guard_name', 'web')->first();
        if ($panelUser !== null) {
            $user->assignRole($panelUser);
        }

        if (in_array('inventory', $activeModules, true)) {
            $inventoryAdmin = Role::query()->where('name', 'inventory_admin')->where('guard_name', 'web')->first();
            if ($inventoryAdmin !== null) {
                $user->assignRole($inventoryAdmin);
            }
        }
    }
}
