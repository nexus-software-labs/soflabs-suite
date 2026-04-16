<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InventoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /** @var array<string, string> $customPermissions */
        $customPermissions = config('filament-shield.custom_permissions', [
            'inventory.intake.create' => 'Create Intake Documents',
            'inventory.intake.approve' => 'Approve Intake Documents',
            'inventory.adjustments.create' => 'Create Inventory Adjustments',
            'inventory.outbound.request' => 'Create Outbound Requests',
            'inventory.outbound.reserve' => 'Reserve Outbound Requests',
            'inventory.outbound.dispatch' => 'Dispatch Outbound Requests',
        ]);
        $permissionNames = array_keys($customPermissions);

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $inventoryAdmin = Role::findOrCreate('inventory_admin', 'web');
        $inventoryOperator = Role::findOrCreate('inventory_operator', 'web');
        $inventoryAuditor = Role::findOrCreate('inventory_auditor', 'web');

        $inventoryAdminPermissions = Permission::query()->whereIn('name', $permissionNames)->where('guard_name', 'web')->get();
        $operatorPermissions = Permission::query()
            ->whereIn('name', ['inventory.intake.create', 'inventory.outbound.request', 'inventory.outbound.reserve'])
            ->where('guard_name', 'web')
            ->get();

        $inventoryAdmin->syncPermissions($inventoryAdminPermissions);
        $inventoryOperator->syncPermissions($operatorPermissions);
        $inventoryAuditor->syncPermissions([]);

        $tenantAdmin = User::query()->where('is_tenant_admin', true)->first();
        if ($tenantAdmin !== null) {
            $tenantAdmin->assignRole('inventory_admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
