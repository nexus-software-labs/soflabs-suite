<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'name',
    'email',
    'password',
    'tenant_id',
    'branch_id',
    'is_tenant_admin',
    'is_super_admin',
    'is_active',
    'last_seen_at',
    'settings',
    'avatar',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
/**
 * Usuarios en base central (tenant_id); {@see CentralConnection} evita la conexión tenant
 * por defecto de Stancl al autenticar o consultar el modelo User.
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use CentralConnection, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_tenant_admin' => 'boolean',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            /*
             * El panel admin usa {@see \App\Http\Middleware\EnsureSuperAdmin} tras Authenticate
             * para redirigir a quien no sea super admin; aquí solo exigimos sesión válida.
             */
            'admin' => true,
            'app' => $this->tenant_id !== null && ! $this->is_super_admin,
            default => false,
        };
    }
}
