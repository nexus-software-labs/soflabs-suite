<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Actions;

use App\Events\TenantProvisioned;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Branch;
use App\Models\Tenant;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Acción de aprovisionamiento de tenant (invocada tras crear un inquilino desde Filament).
 */
final class ProvisionTenantAction
{
    /**
     * Aprovisiona dominio, sucursal principal, módulos, migraciones (si aplica) y dispara el evento.
     *
     * @param  array<string, mixed>  $rawFormState
     */
    public static function execute(Tenant $tenant, array $rawFormState): void
    {
        $selected = $rawFormState['active_modules'] ?? [];
        $selected = is_array($selected) ? $selected : [];

        try {
            DB::transaction(function () use ($tenant, $selected): void {
                $domain = config('app.domain');

                if (! filled($domain)) {
                    throw new RuntimeException('config(app.domain) no está definido; no se puede crear el dominio del tenant.');
                }

                $tenant->createDomain([
                    'domain' => $tenant->getKey().'.'.$domain,
                ]);

                Branch::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'name' => $tenant->company_name ?: $tenant->getKey(),
                    'code' => 'MAIN',
                    'is_main' => true,
                    'is_active' => true,
                ]);

                TenantResource::syncTenantModules($tenant, $selected);

                if (in_array($tenant->db_mode, ['dedicated', 'schema'], true)) {
                    $tenant->run(function (): void {
                        Artisan::call('tenancy:migrate');
                    });
                }

                TenantProvisioned::dispatch($tenant);
            });

            Notification::make()
                ->title('Tenant provisionado correctamente con dominio y sucursal principal.')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Error al provisionar el tenant')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
