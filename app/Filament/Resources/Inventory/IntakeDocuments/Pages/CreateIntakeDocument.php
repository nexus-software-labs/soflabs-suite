<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments\Pages;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use App\Models\Inventory\IntakeDocument;
use App\Services\Inventory\IntakeDocumentWorkflowService;
use App\Services\TenantContext;
use DomainException;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIntakeDocument extends CreateRecord
{
    protected static string $resource = IntakeDocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $tenantId = app(TenantContext::class)->getTenantId();

        if ($tenantId === null) {
            throw new DomainException('No se pudo resolver el tenant para crear el documento de entrada.');
        }

        /** @var IntakeDocument $document */
        $document = app(IntakeDocumentWorkflowService::class)->createManualDocument(
            tenantId: $tenantId,
            warehouseId: (string) $data['warehouse_id'],
            createdBy: auth()->id(),
            lines: $data['lines'] ?? [],
            supplierId: $data['supplier_id'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            documentDate: $data['document_date'] ?? null,
            currencyCode: $data['currency_code'] ?? 'USD',
        );

        return $document;
    }
}
