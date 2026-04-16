<?php

declare(strict_types=1);

use App\Jobs\Inventory\ProcessIntakeDocumentWithAiJob;
use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Tenant;
use App\Services\Inventory\IntakeDocumentWorkflowService;
use Illuminate\Support\Facades\Bus;

test('manual intake document is created in review with lines', function () {
    [$tenant, $supplier, $warehouse, $product] = intakeContext();

    $document = app(IntakeDocumentWorkflowService::class)->createManualDocument(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: null,
        supplierId: $supplier->id,
        documentNumber: 'INV-001',
        lines: [
            [
                'product_id' => $product->id,
                'description_original' => 'Item line',
                'quantity' => 3,
                'subtotal' => 30,
            ],
        ],
    );

    expect($document->status)->toBe('review')
        ->and($document->origin)->toBe('manual')
        ->and($document->lines)->toHaveCount(1);
});

test('approving intake document creates inbound movement and updates stock', function () {
    [$tenant, $supplier, $warehouse, $product] = intakeContext();
    $service = app(IntakeDocumentWorkflowService::class);

    $document = $service->createManualDocument(
        tenantId: $tenant->id,
        warehouseId: $warehouse->id,
        createdBy: null,
        supplierId: $supplier->id,
        documentNumber: 'INV-002',
        lines: [
            [
                'product_id' => $product->id,
                'description_original' => 'Inbound item',
                'quantity' => 5,
                'subtotal' => 50,
            ],
        ],
    );

    $approved = $service->approve($document);

    $stock = Stock::query()
        ->where('tenant_id', $tenant->id)
        ->where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->firstOrFail();

    expect($approved->status)->toBe('approved')
        ->and((float) $stock->quantity)->toBe(5.0);
});

test('ai processing job is queued for received document', function () {
    Bus::fake();

    $document = IntakeDocument::factory()->create([
        'status' => 'received',
        'origin' => 'ai',
    ]);

    app(IntakeDocumentWorkflowService::class)->queueAiProcessing($document);

    Bus::assertDispatched(ProcessIntakeDocumentWithAiJob::class, function (ProcessIntakeDocumentWithAiJob $job) use ($document) {
        return $job->documentId === $document->id;
    });
});

test('document can be rejected with reason', function () {
    $document = IntakeDocument::factory()->create([
        'status' => 'review',
    ]);

    $rejected = app(IntakeDocumentWorkflowService::class)->reject($document, 'Supplier mismatch');

    expect($rejected->status)->toBe('rejected')
        ->and($rejected->rejection_reason)->toBe('Supplier mismatch')
        ->and($rejected->rejected_at)->not->toBeNull();
});

/**
 * @return array{Tenant, Supplier, Warehouse, Product}
 */
function intakeContext(): array
{
    $tenant = Tenant::factory()->create();
    $section = Section::factory()->create(['tenant_id' => $tenant->id]);
    $family = Family::factory()->create(['tenant_id' => $tenant->id, 'section_id' => $section->id]);
    $group = Group::factory()->create(['tenant_id' => $tenant->id, 'family_id' => $family->id]);
    $purchaseUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Purchase Unit F4', 'abbreviation' => 'P4U']);
    $stockUnit = Unit::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Stock Unit F4', 'abbreviation' => 'S4U']);
    $warehouse = Warehouse::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Warehouse F4']);
    $supplier = Supplier::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Supplier F4']);

    $product = Product::create([
        'tenant_id' => $tenant->id,
        'group_id' => $group->id,
        'sku' => fake()->unique()->bothify('F4-####'),
        'name' => 'Product F4',
        'purchase_unit_id' => $purchaseUnit->id,
        'stock_unit_id' => $stockUnit->id,
        'valuation_method' => 'fifo',
        'status' => 'active',
    ]);

    return [$tenant, $supplier, $warehouse, $product];
}
