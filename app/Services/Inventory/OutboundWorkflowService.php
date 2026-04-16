<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\OutboundRequestLine;
use App\Models\Inventory\Stock;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class OutboundWorkflowService
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    /**
     * @param  array<int, array{
     *   product_id: string,
     *   requested_quantity: float|int|string
     * }>  $lines
     */
    public function createRequest(
        string $tenantId,
        string $warehouseId,
        ?string $createdBy,
        array $lines,
        ?string $requestedByName = null,
        ?string $destination = null,
        ?string $requestNumber = null,
    ): OutboundRequest {
        $this->assertPermission($createdBy, 'inventory.outbound.request');

        if ($lines === []) {
            throw new DomainException('An outbound request requires at least one line.');
        }

        return DB::transaction(function () use ($tenantId, $warehouseId, $createdBy, $lines, $requestedByName, $destination, $requestNumber): OutboundRequest {
            $request = OutboundRequest::create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $warehouseId,
                'request_number' => $requestNumber,
                'requested_by_name' => $requestedByName,
                'destination' => $destination,
                'status' => 'requested',
                'created_by' => $createdBy,
            ]);

            foreach ($lines as $index => $line) {
                OutboundRequestLine::create([
                    'tenant_id' => $tenantId,
                    'outbound_request_id' => $request->id,
                    'line_number' => $index + 1,
                    'product_id' => $line['product_id'],
                    'requested_quantity' => (float) $line['requested_quantity'],
                    'status' => 'requested',
                ]);
            }

            return $request->fresh('lines');
        });
    }

    public function reserve(OutboundRequest $request, ?string $processedBy = null): OutboundRequest
    {
        $this->assertPermission($processedBy, 'inventory.outbound.reserve');

        if ($request->status !== 'requested') {
            throw new DomainException('Only requested outbound requests can be reserved.');
        }

        return DB::transaction(function () use ($request, $processedBy): OutboundRequest {
            $lines = $request->lines()->lockForUpdate()->get();
            foreach ($lines as $line) {
                $stock = Stock::query()
                    ->where('tenant_id', $request->tenant_id)
                    ->where('product_id', $line->product_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock === null) {
                    throw new DomainException('Stock record was not found for one outbound line.');
                }

                $available = (float) $stock->quantity - (float) $stock->reserved_quantity;
                $requestedQuantity = (float) $line->requested_quantity;

                if ($available < $requestedQuantity) {
                    throw new DomainException('Insufficient available stock to reserve the outbound request.');
                }

                $stock->update([
                    'reserved_quantity' => (float) $stock->reserved_quantity + $requestedQuantity,
                    'updated_by' => $processedBy,
                ]);

                $line->update([
                    'reserved_quantity' => $requestedQuantity,
                    'status' => 'reserved',
                ]);
            }

            $request->update([
                'status' => 'reserved',
                'processed_by' => $processedBy,
                'reserved_at' => now(),
            ]);

            return $request->fresh('lines');
        });
    }

    public function dispatch(OutboundRequest $request, ?string $processedBy = null): OutboundRequest
    {
        $this->assertPermission($processedBy, 'inventory.outbound.dispatch');

        if ($request->status !== 'reserved') {
            throw new DomainException('Only reserved outbound requests can be dispatched.');
        }

        return DB::transaction(function () use ($request, $processedBy): OutboundRequest {
            $lines = $request->lines()->lockForUpdate()->get();
            foreach ($lines as $line) {
                $reservedQuantity = (float) $line->reserved_quantity;
                $requestedQuantity = (float) $line->requested_quantity;

                if ($reservedQuantity < $requestedQuantity) {
                    throw new DomainException('Line reservation is lower than requested quantity.');
                }

                $stock = Stock::query()
                    ->where('tenant_id', $request->tenant_id)
                    ->where('product_id', $line->product_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock === null) {
                    throw new DomainException('Stock record was not found for one outbound line.');
                }

                $remainingReserved = (float) $stock->reserved_quantity - $requestedQuantity;
                if ($remainingReserved < 0) {
                    throw new DomainException('Reserved stock cannot become negative.');
                }

                $stock->update([
                    'reserved_quantity' => $remainingReserved,
                    'updated_by' => $processedBy,
                ]);

                $this->stockMovementService->registerOutbound(
                    tenantId: $request->tenant_id,
                    productId: $line->product_id,
                    warehouseId: $request->warehouse_id,
                    quantity: $requestedQuantity,
                    performedBy: $processedBy,
                    referenceType: 'outbound_request',
                    referenceId: $request->id,
                    notes: 'Outbound generated from dispatch workflow.',
                );

                $line->update([
                    'dispatched_quantity' => $requestedQuantity,
                    'status' => 'dispatched',
                ]);
            }

            $request->update([
                'status' => 'dispatched',
                'processed_by' => $processedBy,
                'dispatched_at' => now(),
            ]);

            return $request->fresh('lines');
        });
    }

    protected function assertPermission(?string $userId, string $permission): void
    {
        if ($userId === null) {
            return;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            throw new DomainException('The performer user does not exist.');
        }

        if (! $user->can($permission)) {
            throw new DomainException("Missing required permission: {$permission}");
        }
    }
}
