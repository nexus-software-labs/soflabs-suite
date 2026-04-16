<?php

declare(strict_types=1);

use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseZone;
use App\Models\Tenant;
use Illuminate\Database\QueryException;

test('supplier name is unique per tenant and reusable between tenants', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Supplier::create([
        'tenant_id' => $tenantA->id,
        'name' => 'Global Supplier',
        'tax_id' => 'TAX-001',
        'supplier_type' => 'distributor',
        'payment_terms' => 'net_30',
        'status' => 'active',
    ]);

    expect(fn () => Supplier::create([
        'tenant_id' => $tenantA->id,
        'name' => 'Global Supplier',
        'tax_id' => 'TAX-002',
        'supplier_type' => 'distributor',
        'payment_terms' => 'net_30',
        'status' => 'active',
    ]))->toThrow(QueryException::class);

    $supplierForTenantB = Supplier::create([
        'tenant_id' => $tenantB->id,
        'name' => 'Global Supplier',
        'tax_id' => 'TAX-003',
        'supplier_type' => 'manufacturer',
        'payment_terms' => 'cash',
        'status' => 'active',
    ]);

    expect($supplierForTenantB->exists)->toBeTrue();
});

test('stock keeps one row per tenant product and warehouse', function () {
    $tenant = Tenant::factory()->create();
    $section = Section::factory()->create(['tenant_id' => $tenant->id]);
    $family = Family::factory()->create(['tenant_id' => $tenant->id, 'section_id' => $section->id]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'family_id' => $family->id]);
    $purchaseUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Purchase Unit', 'abbreviation' => 'PU01']);
    $stockUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Stock Unit', 'abbreviation' => 'SU01']);
    $warehouse = Warehouse::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Main Warehouse']);

    $product = Product::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'sku' => 'STOCK-001',
        'name' => 'Stock Product',
        'purchase_unit_id' => $purchaseUnit->id,
        'stock_unit_id' => $stockUnit->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    Stock::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 10,
    ]);

    expect(fn () => Stock::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 200,
        'reserved_quantity' => 20,
    ]))->toThrow(QueryException::class);
});

test('warehouse zone requires an existing warehouse', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => WarehouseZone::create([
        'tenant_id' => $tenant->id,
        'warehouse_id' => '01JZZZZZZZZZZZZZZZZZZZZZZZ',
        'name' => 'Cold Zone',
        'storage_condition' => 'refrigerated',
        'status' => 'active',
    ]))->toThrow(QueryException::class);
});
