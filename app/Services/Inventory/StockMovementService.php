<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\Adjustment;
use App\Models\Inventory\Movement;
use App\Models\Inventory\Stock;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function registerInbound(
        string $tenantId,
        string $productId,
        string $warehouseId,
        float $quantity,
        ?string $performedBy = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $notes = null,
    ): Movement {
        $this->assertPermission($performedBy, 'inventory.intake.approve');

        return $this->registerMovement(
            tenantId: $tenantId,
            productId: $productId,
            warehouseId: $warehouseId,
            movementType: 'inbound',
            quantity: $quantity,
            performedBy: $performedBy,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes,
        );
    }

    public function registerOutbound(
        string $tenantId,
        string $productId,
        string $warehouseId,
        float $quantity,
        ?string $performedBy = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $notes = null,
    ): Movement {
        $this->assertPermission($performedBy, 'inventory.outbound.dispatch');

        return $this->registerMovement(
            tenantId: $tenantId,
            productId: $productId,
            warehouseId: $warehouseId,
            movementType: 'outbound',
            quantity: $quantity,
            performedBy: $performedBy,
            referenceType: $referenceType,
            referenceId: $referenceId,
            notes: $notes,
        );
    }

    public function registerAdjustment(
        string $tenantId,
        string $productId,
        string $warehouseId,
        float $differenceQuantity,
        string $reason,
        ?string $performedBy = null,
        ?string $evidencePath = null,
        ?string $notes = null,
    ): Adjustment {
        $this->assertPermission($performedBy, 'inventory.adjustments.create');

        if ($differenceQuantity == 0.0) {
            throw new DomainException('Adjustment difference must be non-zero.');
        }

        $movementType = $differenceQuantity > 0 ? 'adjustment_increase' : 'adjustment_decrease';

        /** @var Movement $movement */
        $movement = $this->registerMovement(
            tenantId: $tenantId,
            productId: $productId,
            warehouseId: $warehouseId,
            movementType: $movementType,
            quantity: abs($differenceQuantity),
            performedBy: $performedBy,
            referenceType: 'inventory_adjustment',
            referenceId: null,
            notes: $notes,
        );

        return Adjustment::create([
            'tenant_id' => $tenantId,
            'movement_id' => $movement->id,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'adjustment_type' => $differenceQuantity > 0 ? 'positive' : 'negative',
            'difference_quantity' => abs($differenceQuantity),
            'reason' => $reason,
            'evidence_path' => $evidencePath,
            'notes' => $notes,
            'performed_by' => $performedBy,
            'adjusted_at' => now(),
        ]);
    }

    protected function registerMovement(
        string $tenantId,
        string $productId,
        string $warehouseId,
        string $movementType,
        float $quantity,
        ?string $performedBy,
        ?string $referenceType,
        ?string $referenceId,
        ?string $notes,
    ): Movement {
        if ($quantity <= 0) {
            throw new DomainException('Movement quantity must be greater than zero.');
        }

        return DB::transaction(function () use (
            $tenantId,
            $productId,
            $warehouseId,
            $movementType,
            $quantity,
            $performedBy,
            $referenceType,
            $referenceId,
            $notes,
        ): Movement {
            /** @var Stock $stock */
            $stock = Stock::query()
                ->where('tenant_id', $tenantId)
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'product_id' => $productId,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                    ],
                );

            $stockBefore = (float) $stock->quantity;
            $stockAfter = match ($movementType) {
                'inbound', 'adjustment_increase' => $stockBefore + $quantity,
                'outbound', 'adjustment_decrease' => $stockBefore - $quantity,
                default => throw new DomainException('Unsupported movement type.'),
            };

            if ($stockAfter < 0) {
                throw new DomainException('Insufficient stock for this movement.');
            }

            $stock->update([
                'quantity' => $stockAfter,
                'updated_by' => $performedBy,
            ]);

            return Movement::create([
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'performed_by' => $performedBy,
                'moved_at' => now(),
            ]);
        });
    }

    /**
     * @return Collection<int, Movement>
     */
    public function kardex(string $tenantId, string $productId, string $warehouseId, ?string $from = null, ?string $to = null): Collection
    {
        return Movement::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when($from !== null, fn ($query) => $query->where('moved_at', '>=', $from))
            ->when($to !== null, fn ($query) => $query->where('moved_at', '<=', $to))
            ->orderBy('moved_at')
            ->get();
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
