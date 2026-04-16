<?php

declare(strict_types=1);

namespace App\Jobs\Inventory;

use App\Models\Inventory\IntakeDocument;
use App\Services\Inventory\IntakeDocumentWorkflowService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIntakeDocumentWithAiJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $documentId) {}

    public function handle(IntakeDocumentWorkflowService $workflowService): void
    {
        $document = IntakeDocument::query()->find($this->documentId);

        if ($document === null) {
            return;
        }

        $sourceText = (string) ($document->raw_extraction['source_text'] ?? 'No source text provided');
        $workflowService->processWithAi($document, $sourceText);
    }
}
