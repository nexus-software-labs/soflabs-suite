<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Concede al administrador del tenant permisos Shield y de inventario dentro
 * del panel app y del contexto tenancy, sin afectar llaves del panel central.
 */
final class TenantAdminGate
{
    /**
     * @return bool|null true si el tenant admin puede la habilidad; null para seguir evaluando el resto de gates.
     */
    public static function before(?Authenticatable $user, string $ability): ?bool
    {
        if (! $user instanceof User) {
            return null;
        }

        if (! $user->is_tenant_admin || $user->tenant_id === null) {
            return null;
        }

        if ($user->is_super_admin) {
            return null;
        }

        if (in_array($ability, ['viewAdmin', 'isAdmin'], true)) {
            return null;
        }

        if (str_starts_with($ability, 'inventory.')) {
            return true;
        }

        if (! str_contains($ability, ':')) {
            return null;
        }

        if (Filament::getCurrentPanel()?->getId() !== 'app') {
            return null;
        }

        if (! tenancy()->initialized) {
            return null;
        }

        return true;
    }
}
