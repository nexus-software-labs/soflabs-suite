<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PrintOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintOrderApiController extends Controller
{
    public function __construct(
        protected PrintOrderService $printOrderService,
    ) {}

    /**
     * Configuración de precios y límites para el flujo de impresión (frontend).
     */
    public function getConfig(): JsonResponse
    {
        return response()->json($this->printOrderService->getPricesConfiguration());
    }

    /**
     * Conteo de páginas y metadatos por archivo subido.
     */
    public function analyzeFiles(Request $request): JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:51200'],
        ]);

        $payload = $this->printOrderService->analyzeFiles($request->file('files', []));

        return response()->json($payload);
    }

    /**
     * Cotización según {@see PrintOrderService::calculateOrderTotal}.
     *
     * @return array{breakdown: array<string, float|int|string>, total: float, items_summary: array<string, mixed>}
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_pages' => ['required', 'integer', 'min:1'],
            'config' => ['required', 'array'],
            'config.service_type' => ['sometimes', 'string'],
            'config.print_type' => ['required', 'string', 'in:bw,color'],
            'config.paper_size' => ['required', 'string'],
            'config.copies' => ['required', 'integer', 'min:1'],
            'config.paper_type' => ['sometimes', 'nullable', 'string'],
            'config.double_sided' => ['sometimes', 'boolean'],
            'config.binding' => ['sometimes', 'boolean'],
            'config.business_cards' => ['sometimes', 'integer', 'min:0'],
            'config.paper_cutting' => ['sometimes', 'boolean'],
            'delivery' => ['sometimes', 'array'],
            'delivery.method' => ['sometimes', 'string'],
            'delivery.is_national' => ['sometimes', 'boolean'],
            'delivery.distance' => ['sometimes', 'numeric'],
        ]);

        $orderData = [
            'total_pages' => (int) $validated['total_pages'],
            'config' => $validated['config'],
            'delivery' => $validated['delivery'] ?? [],
        ];

        $result = $this->printOrderService->calculateOrderTotal($orderData);

        return response()->json($result);
    }
}
