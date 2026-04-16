<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests\Pages;

use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use App\Models\Inventory\OutboundRequest;
use App\Services\Inventory\OutboundWorkflowService;
use App\Services\TenantContext;
use DomainException;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOutboundRequest extends CreateRecord
{
    protected static string $resource = OutboundRequestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $tenantId = app(TenantContext::class)->getTenantId();

        if ($tenantId === null) {
            throw new DomainException('No se pudo resolver el tenant para crear la solicitud de salida.');
        }

        /** @var OutboundRequest $request */
        $request = app(OutboundWorkflowService::class)->createRequest(
            tenantId: $tenantId,
            warehouseId: (string) $data['warehouse_id'],
            createdBy: auth()->id(),
            lines: $data['lines'] ?? [],
            requestedByName: $data['requested_by_name'] ?? null,
            destination: $data['destination'] ?? null,
            requestNumber: $data['request_number'] ?? null,
        );

        return $request;
    }
}
