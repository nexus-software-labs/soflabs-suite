<?php

declare(strict_types=1);

use App\Models\Inventory\Adjustment;
use App\Models\Inventory\Movement;
use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use Database\Seeders\InventoryDevelopmentSeeder;
use Database\Seeders\InventoryPermissionSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\TenantSeeder;

test('inventory development seeders create realistic demo dataset', function () {
    $this->seed([
        PlanSeeder::class,
        TenantSeeder::class,
        InventoryPermissionSeeder::class,
        InventoryDevelopmentSeeder::class,
    ]);

    $tenant = Tenant::query()->findOrFail('demo');

    expect(Section::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Warehouse::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Supplier::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Product::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Stock::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(OutboundRequest::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Movement::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(Adjustment::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0);
});
