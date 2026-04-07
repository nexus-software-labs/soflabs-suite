<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::query()->where('slug', 'professional')->firstOrFail();

        $tenant = Tenant::withoutEvents(function () use ($plan): Tenant {
            return Tenant::query()->updateOrCreate(
                ['id' => 'demo'],
                [
                    'plan_id' => $plan->id,
                    'db_mode' => 'shared',
                    'is_active' => true,
                    'company_name' => 'Empresa Demo',
                    'trial_ends_at' => null,
                    'subscribed_at' => now(),
                ],
            );
        });

        /*
         * Con {@see \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain}, el resolver
         * compara solo el primer segmento del host (p. ej. "demo" en demo.software-labs.test).
         * En `domains.domain` debe ir ese segmento, no el FQDN completo.
         */
        $baseDomain = config('app.domain');
        if (filled($baseDomain)) {
            $tenant->domains()->updateOrCreate(
                ['domain' => 'demo'],
                ['tenant_id' => $tenant->id],
            );
        }

        Branch::query()->withTrashed()->where('tenant_id', $tenant->id)->forceDelete();

        $mainBranch = Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Casa Matriz',
            'is_main' => true,
            'is_active' => true,
        ]);

        Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Sucursal Norte',
            'is_main' => false,
            'is_active' => true,
        ]);

        TenantModule::query()->where('tenant_id', $tenant->id)->delete();

        foreach (['inventory', 'packages', 'printing'] as $module) {
            TenantModule::query()->create([
                'tenant_id' => $tenant->id,
                'module' => $module,
                'is_active' => true,
                'activated_at' => now(),
            ]);
        }

        $superEmail = filled($baseDomain) ? 'super@'.$baseDomain : 'super@localhost';
        $tenantAdminEmail = filled($baseDomain) ? 'admin@demo.'.$baseDomain : 'admin@demo.localhost';

        User::query()->updateOrCreate(
            ['email' => $tenantAdminEmail],
            [
                'name' => 'Administrador Demo',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'branch_id' => $mainBranch->id,
                'is_tenant_admin' => true,
                'is_super_admin' => false,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super administrador',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'branch_id' => null,
                'is_tenant_admin' => false,
                'is_super_admin' => true,
            ],
        );
    }
}
