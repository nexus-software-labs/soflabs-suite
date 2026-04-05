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
         * InitializeTenancyBySubdomain resuelve el inquilino por el primer segmento del host.
         * Para acceder como demo.{APP_DOMAIN} (p. ej. demo.myapp.test), el registro debe ser "demo".
         */
        $tenant->domains()->updateOrCreate(
            ['domain' => 'demo'],
            [],
        );

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

        foreach (['inventory', 'packages'] as $module) {
            TenantModule::query()->create([
                'tenant_id' => $tenant->id,
                'module' => $module,
                'is_active' => true,
                'activated_at' => now(),
            ]);
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@demo.myapp.test'],
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
            ['email' => 'super@myapp.test'],
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
