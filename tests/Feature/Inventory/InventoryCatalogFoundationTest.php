<?php

declare(strict_types=1);

use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Unit;
use App\Models\Tenant;
use Illuminate\Database\QueryException;

test('sku is unique per tenant and reusable across tenants', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $sectionA = Section::factory()->create(['tenant_id' => $tenantA->id]);
    $familyA = Family::factory()->create(['tenant_id' => $tenantA->id, 'section_id' => $sectionA->id]);
    $groupA = Group::factory()->create(['tenant_id' => $tenantA->id, 'family_id' => $familyA->id]);
    $purchaseUnitA = Unit::factory()->create(['tenant_id' => $tenantA->id, 'abbreviation' => 'BOXA', 'name' => 'Box A']);
    $stockUnitA = Unit::factory()->create(['tenant_id' => $tenantA->id, 'abbreviation' => 'UNTA', 'name' => 'Unit A']);

    Product::create([
        'tenant_id' => $tenantA->id,
        'group_id' => $groupA->id,
        'sku' => 'SKU-001',
        'name' => 'Product A',
        'purchase_unit_id' => $purchaseUnitA->id,
        'stock_unit_id' => $stockUnitA->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    expect(fn () => Product::create([
        'tenant_id' => $tenantA->id,
        'group_id' => $groupA->id,
        'sku' => 'SKU-001',
        'name' => 'Duplicate Product',
        'purchase_unit_id' => $purchaseUnitA->id,
        'stock_unit_id' => $stockUnitA->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]))->toThrow(QueryException::class);

    $sectionB = Section::factory()->create(['tenant_id' => $tenantB->id]);
    $familyB = Family::factory()->create(['tenant_id' => $tenantB->id, 'section_id' => $sectionB->id]);
    $groupB = Group::factory()->create(['tenant_id' => $tenantB->id, 'family_id' => $familyB->id]);
    $purchaseUnitB = Unit::factory()->create(['tenant_id' => $tenantB->id, 'abbreviation' => 'BOXB', 'name' => 'Box B']);
    $stockUnitB = Unit::factory()->create(['tenant_id' => $tenantB->id, 'abbreviation' => 'UNTB', 'name' => 'Unit B']);

    $product = Product::create([
        'tenant_id' => $tenantB->id,
        'group_id' => $groupB->id,
        'sku' => 'SKU-001',
        'name' => 'Product B',
        'purchase_unit_id' => $purchaseUnitB->id,
        'stock_unit_id' => $stockUnitB->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    expect($product->exists)->toBeTrue();
});

test('product requires an existing inventory group', function () {
    $tenant = Tenant::factory()->create();
    $purchaseUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'abbreviation' => 'PKG1', 'name' => 'Package 1']);
    $stockUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'abbreviation' => 'ITM1', 'name' => 'Item 1']);

    expect(fn () => Product::create([
        'tenant_id' => $tenant->id,
        'group_id' => '01JZZZZZZZZZZZZZZZZZZZZZZZ',
        'sku' => 'SKU-999',
        'name' => 'Invalid Product',
        'purchase_unit_id' => $purchaseUnit->id,
        'stock_unit_id' => $stockUnit->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]))->toThrow(QueryException::class);
});
