<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Jobs\Inventory\ProcessIntakeDocumentWithAiJob;
use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\IntakeDocumentLine;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

use function Laravel\Ai\agent;

class IntakeDocumentWorkflowService
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    /**
     * @param  array<int, array{
     *   product_id?: string|null,
     *   description_original: string,
     *   quantity: float|int|string,
     *   unit_id?: string|null,
     *   unit_price?: float|int|string|null,
     *   subtotal?: float|int|string|null,
     *   linked_manually?: bool,
     *   status?: string
     * }>  $lines
     */
    public function createManualDocument(
        string $tenantId,
        string $warehouseId,
        ?string $createdBy,
        array $lines,
        ?string $supplierId = null,
        ?string $documentNumber = null,
        ?string $documentDate = null,
        ?string $currencyCode = 'USD',
    ): IntakeDocument {
        $this->assertPermission($createdBy, 'inventory.intake.create');

        if ($lines === []) {
            throw new DomainException('A manual intake document requires at least one line.');
        }

        return DB::transaction(function () use (
            $tenantId,
            $warehouseId,
            $createdBy,
            $lines,
            $supplierId,
            $documentNumber,
            $documentDate,
            $currencyCode
        ): IntakeDocument {
            $document = IntakeDocument::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'document_number' => $documentNumber,
                'document_date' => $documentDate,
                'currency_code' => $currencyCode ?? 'USD',
                'status' => 'review',
                'origin' => 'manual',
                'created_by' => $createdBy,
            ]);

            foreach ($lines as $index => $line) {
                IntakeDocumentLine::create([
                    'tenant_id' => $tenantId,
                    'intake_document_id' => $document->id,
                    'line_number' => $index + 1,
                    'product_id' => $line['product_id'] ?? null,
                    'description_original' => $line['description_original'],
                    'quantity' => (float) $line['quantity'],
                    'unit_id' => $line['unit_id'] ?? null,
                    'unit_price' => isset($line['unit_price']) ? (float) $line['unit_price'] : null,
                    'subtotal' => isset($line['subtotal']) ? (float) $line['subtotal'] : null,
                    'linked_manually' => (bool) ($line['linked_manually'] ?? true),
                    'status' => $line['status'] ?? 'pending_review',
                ]);
            }

            $totals = $document->lines()->selectRaw('SUM(subtotal) as subtotal')->first();
            $document->update([
                'subtotal' => $totals?->subtotal ?? null,
                'total' => $totals?->subtotal ?? null,
            ]);

            return $document->fresh(['lines']);
        });
    }

    public function queueAiProcessing(IntakeDocument|string $document): void
    {
        $documentId = $document instanceof IntakeDocument ? $document->id : $document;
        ProcessIntakeDocumentWithAiJob::dispatch($documentId);
    }

    public function processWithAi(IntakeDocument $document, string $documentText): IntakeDocument
    {
        if (! in_array($document->status, ['received', 'processing'], true)) {
            throw new DomainException('Document is not in a processable state.');
        }

        $document->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);

        $response = agent(
            instructions: 'Extract inventory intake data from the document. Return strictly valid JSON with keys: document_number, supplier_name, lines[].description, lines[].quantity, lines[].unit_price, totals.subtotal, totals.tax, totals.total, confidence, warnings[].',
        )->prompt($documentText);

        $decoded = json_decode($response->text, true);

        if (! is_array($decoded)) {
            $decoded = [
                'document_number' => null,
                'lines' => [],
                'totals' => ['subtotal' => null, 'tax' => null, 'total' => null],
                'confidence' => 0,
                'warnings' => ['Invalid AI response format'],
            ];
        }

        return DB::transaction(function () use ($document, $decoded): IntakeDocument {
            $document->lines()->delete();

            $lines = is_array($decoded['lines'] ?? null) ? $decoded['lines'] : [];
            foreach ($lines as $index => $line) {
                IntakeDocumentLine::create([
                    'tenant_id' => $document->tenant_id,
                    'intake_document_id' => $document->id,
                    'line_number' => $index + 1,
                    'description_original' => (string) ($line['description'] ?? 'Unknown item'),
                    'quantity' => (float) ($line['quantity'] ?? 0),
                    'unit_price' => isset($line['unit_price']) ? (float) $line['unit_price'] : null,
                    'subtotal' => isset($line['subtotal']) ? (float) $line['subtotal'] : null,
                    'status' => 'pending_review',
                ]);
            }

            $totals = is_array($decoded['totals'] ?? null) ? $decoded['totals'] : [];
            $document->update([
                'document_number' => $decoded['document_number'] ?? $document->document_number,
                'subtotal' => isset($totals['subtotal']) ? (float) $totals['subtotal'] : null,
                'tax' => isset($totals['tax']) ? (float) $totals['tax'] : null,
                'total' => isset($totals['total']) ? (float) $totals['total'] : null,
                'ai_confidence' => isset($decoded['confidence']) ? (float) $decoded['confidence'] : null,
                'warnings' => is_array($decoded['warnings'] ?? null) ? $decoded['warnings'] : [],
                'raw_extraction' => $decoded,
                'status' => 'review',
            ]);

            return $document->fresh(['lines']);
        });
    }

    public function approve(IntakeDocument $document, ?string $approvedBy = null): IntakeDocument
    {
        $this->assertPermission($approvedBy, 'inventory.intake.approve');

        if ($document->status !== 'review') {
            throw new DomainException('Only documents in review status can be approved.');
        }

        return DB::transaction(function () use ($document, $approvedBy): IntakeDocument {
            $lines = $document->lines()->get();
            foreach ($lines as $line) {
                if ($line->product_id === null) {
                    throw new DomainException('Every intake line must be linked to a product before approval.');
                }

                $this->stockMovementService->registerInbound(
                    tenantId: $document->tenant_id,
                    productId: $line->product_id,
                    warehouseId: $document->warehouse_id,
                    quantity: (float) $line->quantity,
                    performedBy: $approvedBy,
                    referenceType: 'intake_document',
                    referenceId: $document->id,
                    notes: 'Inbound generated from intake document approval.',
                );

                $line->update(['status' => 'approved']);
            }

            $document->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $document->fresh(['lines']);
        });
    }

    public function reject(IntakeDocument $document, string $reason): IntakeDocument
    {
        if (! in_array($document->status, ['review', 'processing', 'received'], true)) {
            throw new DomainException('Document cannot be rejected from the current status.');
        }

        $document->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $document->fresh();
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
