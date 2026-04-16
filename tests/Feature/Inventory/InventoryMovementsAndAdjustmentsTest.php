<?php

declare(strict_types=1);

use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\Movement;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Services\Inventory\StockMovementService;

test('inbound movement increases stock and creates movement trace', function () {
    [$tenant, $product, $warehouse] = inventoryContextWithStock(initialQuantity: 10);

    $movement = app(StockMovementService::class)->registerInbound(
        tenantId: $tenant->id,
        productId: $product->id,
        warehouseId: $warehouse->id,
        quantity: 5,
        referenceType: 'purchase_receipt',
        referenceId: 'PO-001',
    );

    $stock = Stock::query()->where('tenant_id', $tenant->id)->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->firstOrFail();

    expect($movement->movement_type)->toBe('inbound')
        ->and((float) $movement->stock_before)->toBe(10.0)
        ->and((float) $movement->stock_after)->toBe(15.0)
        ->and((float) $stock->quantity)->toBe(15.0);
});

test('outbound movement fails when stock is insufficient', function () {
    [$tenant, $product, $warehouse] = inventoryContextWithStock(initialQuantity: 2);

    expect(fn () => app(StockMovementService::class)->registerOutbound(
        tenantId: $tenant->id,
        productId: $product->id,
        warehouseId: $warehouse->id,
        quantity: 3,
    ))->toThrow(DomainException::class, 'Insufficient stock for this movement.');

    expect(Movement::query()->count())->toBe(0);
});

test('adjustment creates adjustment record and linked movement', function () {
    [$tenant, $product, $warehouse] = inventoryContextWithStock(initialQuantity: 20);

    $adjustment = app(StockMovementService::class)->registerAdjustment(
        tenantId: $tenant->id,
        productId: $product->id,
        warehouseId: $warehouse->id,
        differenceQuantity: -4,
        reason: 'count_error',
        notes: 'Cycle count mismatch',
    );

    $adjustment->refresh();
    $movement = $adjustment->movement;
    $stock = Stock::query()->where('tenant_id', $tenant->id)->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->firstOrFail();

    expect($adjustment->adjustment_type)->toBe('negative')
        ->and((float) $adjustment->difference_quantity)->toBe(4.0)
        ->and($movement->movement_type)->toBe('adjustment_decrease')
        ->and((float) $movement->stock_before)->toBe(20.0)
        ->and((float) $movement->stock_after)->toBe(16.0)
        ->and((float) $stock->quantity)->toBe(16.0);
});

test('kardex returns movements ordered by movement timestamp', function () {
    [$tenant, $product, $warehouse] = inventoryContextWithStock(initialQuantity: 1);
    $service = app(StockMovementService::class);

    $service->registerInbound($tenant->id, $product->id, $warehouse->id, 5);
    $service->registerOutbound($tenant->id, $product->id, $warehouse->id, 2);

    $kardex = $service->kardex($tenant->id, $product->id, $warehouse->id);

    expect($kardex)->toHaveCount(2)
        ->and($kardex->first()->movement_type)->toBe('inbound')
        ->and($kardex->last()->movement_type)->toBe('outbound');
});

/**
 * @return array{Tenant, Product, Warehouse}
 */
function inventoryContextWithStock(float $initialQuantity): array
{
    $tenant = Tenant::factory()->create();
    $section = Section::factory()->create(['tenant_id' => $tenant->id]);
    $family = Family::factory()->create(['tenant_id' => $tenant->id, 'section_id' => $section->id]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'family_id' => $family->id]);
    $purchaseUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Purchase Unit F3', 'abbreviation' => 'P3U']);
    $stockUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Stock Unit F3', 'abbreviation' => 'S3U']);
    $warehouse = Warehouse::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Phase 3 Warehouse']);

    $product = Product::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'sku' => fake()->unique()->bothify('F3-####'),
        'name' => 'Phase 3 Product',
        'purchase_unit_id' => $purchaseUnit->id,
        'stock_unit_id' => $stockUnit->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    Stock::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => $initialQuantity,
        'reserved_quantity' => 0,
    ]);

    return [$tenant, $product, $warehouse];
}
