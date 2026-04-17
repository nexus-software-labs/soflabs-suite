<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Core\Promotion;
use App\Services\PrintOrderService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintOrderApiController extends Controller
{
    public function __construct(
        protected PrintOrderService $printOrderService,
        protected PromotionService $promotionService,
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

    /**
     * Mejor promoción automática para el contexto del pedido (p. ej. envío).
     */
    public function bestPromotion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['sometimes', 'nullable', 'string'],
            'store_id' => ['sometimes', 'nullable', 'string'],
            'service_type' => ['required', 'string'],
            'subtotal' => ['required', 'numeric'],
            'delivery_cost' => ['required', 'numeric'],
            'applies_to' => ['sometimes', 'nullable', 'string'],
        ]);

        $branchId = $validated['branch_id'] ?? $validated['store_id'] ?? null;

        $promotion = $this->promotionService->findBestAutomaticPromotion(
            $branchId,
            $validated['service_type'],
            (float) $validated['subtotal'],
            (float) $validated['delivery_cost'],
            $validated['applies_to'] ?? null,
            null,
        );

        if ($promotion === null) {
            return response()->json([
                'success' => false,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->promotionToFrontendArray($promotion),
        ]);
    }

    /**
     * Valida un cupón manual para el flujo de impresión.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:64'],
            'branch_id' => ['required', 'string'],
            'service_type' => ['required', 'string'],
            'subtotal' => ['required', 'numeric'],
            'delivery_cost' => ['required', 'numeric'],
        ]);

        $result = $this->promotionService->validateCouponCode(
            $validated['coupon_code'],
            $validated['branch_id'],
            $validated['service_type'],
            (float) $validated['subtotal'],
            (float) $validated['delivery_cost'],
        );

        if (! $result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Cupón no válido',
            ], 422);
        }

        /** @var Promotion $promotion */
        $promotion = $result['promotion'];

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? null,
            'data' => $this->promotionToFrontendArray($promotion),
        ]);
    }

    /**
     * @return array<string, float|int|string|null>
     */
    private function promotionToFrontendArray(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id,
            'name' => $promotion->name,
            'description' => $promotion->description,
            'discount_type' => $promotion->discount_type,
            'discount_value' => $promotion->discount_value !== null ? (float) $promotion->discount_value : null,
            'applies_to' => $promotion->applies_to,
            'coupon_code' => $promotion->coupon_code,
            'max_discount_amount' => $promotion->max_discount_amount !== null ? (float) $promotion->max_discount_amount : null,
            'min_order_amount' => $promotion->min_order_amount !== null ? (float) $promotion->min_order_amount : null,
            'label' => $promotion->getDiscountLabel(),
            'application_type' => $promotion->application_type,
        ];
    }
}
