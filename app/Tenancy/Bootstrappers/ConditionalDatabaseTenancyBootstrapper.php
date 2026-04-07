<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant as AppTenant;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * En {@see AppTenant::$db_mode} <code>shared</code> los datos viven en la BD central con
 * {@see TenantScope}; no existe BD <code>tenant{id}</code>.
 * Solo aplica {@see DatabaseTenancyBootstrapper} para <code>dedicated</code> y <code>schema</code>.
 */
final class ConditionalDatabaseTenancyBootstrapper implements TenancyBootstrapper
{
    public function __construct(
        private readonly DatabaseTenancyBootstrapper $databaseTenancyBootstrapper,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        if ($tenant instanceof AppTenant && $tenant->db_mode === 'shared') {
            return;
        }

        $this->databaseTenancyBootstrapper->bootstrap($tenant);
    }

    public function revert(): void
    {
        $this->databaseTenancyBootstrapper->revert();
    }
}
