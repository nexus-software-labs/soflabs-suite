<?php

declare(strict_types=1);

use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Inventory\OutboundWorkflowService;
use Spatie\Permission\Models\Permission;

test('outbound request can be reserved and dispatched', function () {
    [$tenant, $warehouse, $product] = outboundContextWithStock(quantity: 20);
    $service = app(OutboundWorkflowService::class);

    $request = $service->createRequest(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: null,
        lines: [
            ['product_id' => $product->id, 'requested_quantity' => 8],
        ],
        requestNumber: 'OUT-001',
    );

    $reserved = $service->reserve($request);
    $stockAfterReserve = Stock::query()
        ->where('tenant_id', $tenant->id)
        ->where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->firstOrFail();

    expect($reserved->status)->toBe('reserved')
        ->and((float) $stockAfterReserve->quantity)->toBe(20.0)
        ->and((float) $stockAfterReserve->reserved_quantity)->toBe(8.0);

    $dispatched = $service->dispatch($reserved);
    $stockAfterDispatch = Stock::query()
        ->where('tenant_id', $tenant->id)
        ->where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->firstOrFail();

    expect($dispatched->status)->toBe('dispatched')
        ->and((float) $stockAfterDispatch->quantity)->toBe(12.0)
        ->and((float) $stockAfterDispatch->reserved_quantity)->toBe(0.0);
});

test('reserve fails when available stock is insufficient', function () {
    [$tenant, $warehouse, $product] = outboundContextWithStock(quantity: 5);
    $service = app(OutboundWorkflowService::class);

    $request = $service->createRequest(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: null,
        lines: [
            ['product_id' => $product->id, 'requested_quantity' => 10],
        ],
    );

    expect(fn () => $service->reserve($request))
        ->toThrow(DomainException::class, 'Insufficient available stock to reserve the outbound request.');
});

test('dispatch fails when request is not reserved', function () {
    [$tenant, $warehouse, $product] = outboundContextWithStock(quantity: 10);
    $service = app(OutboundWorkflowService::class);

    $request = $service->createRequest(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: null,
        lines: [
            ['product_id' => $product->id, 'requested_quantity' => 3],
        ],
    );

    expect(fn () => $service->dispatch($request))
        ->toThrow(DomainException::class, 'Only reserved outbound requests can be dispatched.');
});

test('creating outbound request with actor requires permission', function () {
    [$tenant, $warehouse, $product] = outboundContextWithStock(quantity: 10);
    $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_super_admin' => false, 'is_tenant_admin' => false]);
    Permission::findOrCreate('inventory.outbound.request');

    expect(fn () => app(OutboundWorkflowService::class)->createRequest(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: (string) $user->id,
        lines: [
            ['product_id' => $product->id, 'requested_quantity' => 2],
        ],
    ))->toThrow(DomainException::class, 'Missing required permission: inventory.outbound.request');
});

/**
 * @return array{Tenant, Warehouse, Product}
 */
function outboundContextWithStock(float $quantity): array
{
    $tenant = Tenant::factory()->create();
    $section = Section::factory()->create(['tenant_id' => $tenant->id]);
    $family = Family::factory()->create(['tenant_id' => $tenant->id, 'section_id' => $section->id]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'family_id' => $family->id]);
    $purchaseUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Purchase Unit F5', 'abbreviation' => 'P5U']);
    $stockUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Stock Unit F5', 'abbreviation' => 'S5U']);
    $warehouse = Warehouse::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Warehouse F5']);

    $product = Product::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'sku' => fake()->unique()->bothify('F5-####'),
        'name' => 'Product F5',
        'purchase_unit_id' => $purchaseUnit->id,
        'stock_unit_id' => $stockUnit->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    Stock::create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => $quantity,
        'reserved_quantity' => 0,
    ]);

    return [$tenant, $warehouse, $product];
}
