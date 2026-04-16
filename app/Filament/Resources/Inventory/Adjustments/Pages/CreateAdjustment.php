<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Adjustments\Pages;

use App\Filament\Resources\Inventory\Adjustments\AdjustmentResource;
use App\Models\Inventory\Adjustment;
use App\Services\Inventory\StockMovementService;
use App\Services\TenantContext;
use DomainException;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdjustment extends CreateRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $tenantId = app(TenantContext::class)->getTenantId();

        if ($tenantId === null) {
            throw new DomainException('No se pudo resolver el tenant para crear el ajuste.');
        }

        $differenceQuantity = (float) $data['difference_quantity'];
        if (($data['adjustment_type'] ?? 'positive') === 'negative') {
            $differenceQuantity *= -1;
        }

        /** @var Adjustment $adjustment */
        $adjustment = app(StockMovementService::class)->registerAdjustment(
            tenantId: $tenantId,
            productId: (string) $data['product_id'],
            warehouseId: (string) $data['warehouse_id'],
            differenceQuantity: $differenceQuantity,
            reason: (string) $data['reason'],
            performedBy: auth()->id(),
            notes: $data['notes'] ?? null,
        );

        return $adjustment;
    }
}
