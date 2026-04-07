<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Core\Promotion;
use App\Models\Core\PromotionApplication;

class PromotionService
{
    /**
     * @param  non-empty-string|null  $branchId  ULID de sucursal
     */
    public function findBestAutomaticPromotion(
        ?string $branchId,
        string $serviceType,
        float $subtotal,
        float $deliveryCost,
        ?string $appliesTo = null,
        ?int $customerId = null
    ): ?Promotion {
        $query = Promotion::where('active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->where('application_type', 'automatic');

        if ($appliesTo) {
            $query->where('applies_to', $appliesTo);
        }

        $query->where(function ($q) use ($serviceType) {
            $q->where('service_type', $serviceType)
                ->orWhere('service_type', 'both');
        });

        $query->where(function ($q) use ($branchId, $customerId) {
            $this->applyScopeQuery($q, $branchId, $customerId);
        });

        $promotions = $query->get();

        $customerSpecificPromotions = [];
        $generalPromotions = [];

        foreach ($promotions as $promotion) {
            if ($promotion->scope_type === 'customers') {
                if ($customerId) {
                    $customerSpecificPromotions[] = $promotion;
                }

                continue;
            }

            $generalPromotions[] = $promotion;
        }

        $promotionsToCheck = ! empty($customerSpecificPromotions)
            ? $customerSpecificPromotions
            : $generalPromotions;

        $bestPromotion = null;
        $bestDiscount = 0;

        foreach ($promotionsToCheck as $promotion) {
            if ($promotion->discount_type === 'fixed_rate' && $promotion->applies_to === 'weight') {
                return $promotion;
            }

            if ($promotion->discount_type === 'fixed_rate') {
                continue;
            }

            $applicableAmount = $promotion->applies_to === 'delivery' ? $deliveryCost : $subtotal;

            if ($promotion->min_order_amount && $applicableAmount < $promotion->min_order_amount) {
                continue;
            }

            $discount = $this->calculateDiscount($promotion, $subtotal, $deliveryCost);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestPromotion = $promotion;
            }
        }

        return $bestPromotion;
    }

    /**
     * @return array{valid: bool, message?: string, promotion?: Promotion, discount_amount?: float}
     */
    public function validateCouponCode(
        string $couponCode,
        string $branchId,
        string $serviceType,
        float $subtotal,
        float $deliveryCost
    ): array {
        $promotion = Promotion::where('coupon_code', strtoupper($couponCode))
            ->where('application_type', 'coupon')
            ->where('active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->first();

        if (! $promotion) {
            return ['valid' => false, 'message' => 'Código de cupón inválido o expirado'];
        }

        if ($promotion->usage_limit && $promotion->times_used >= $promotion->usage_limit) {
            return ['valid' => false, 'message' => 'Este cupón ya alcanzó su límite de usos'];
        }

        if (! $this->appliesToService($promotion, $serviceType)) {
            return ['valid' => false, 'message' => 'Este cupón no aplica a este tipo de servicio'];
        }

        if (! $promotion->appliesToBranch($branchId)) {
            return ['valid' => false, 'message' => 'Este cupón no está disponible en tu ubicación'];
        }

        $applicableAmount = $promotion->applies_to === 'delivery' ? $deliveryCost : $subtotal;
        if ($promotion->min_order_amount && $applicableAmount < $promotion->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Monto mínimo requerido: $'.number_format($promotion->min_order_amount, 2),
            ];
        }

        $discount = $this->calculateDiscount($promotion, $subtotal, $deliveryCost);

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount_amount' => $discount,
            'message' => $promotion->description,
        ];
    }

    /**
     * Promociones automáticas activas para una sucursal.
     *
     * @param  non-empty-string|null  $branchId
     */
    public function getActivePromotionsForBranch(?string $branchId, string $serviceType = 'both')
    {
        return Promotion::where('active', true)
            ->where('application_type', 'automatic')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->where(function ($query) use ($serviceType) {
                $query->where('service_type', $serviceType)
                    ->orWhere('service_type', 'both');
            })
            ->where(function ($query) use ($branchId) {
                if ($branchId) {
                    $this->applyScopeQuery($query, $branchId);
                } else {
                    $query->where('scope_type', 'all');
                }
            })
            ->orderBy('discount_value', 'desc')
            ->get();
    }

    public function calculateDiscount(Promotion $promotion, float $subtotal, float $deliveryCost): float
    {
        $baseAmount = $promotion->applies_to === 'delivery' ? $deliveryCost : $subtotal;

        switch ($promotion->discount_type) {
            case 'free_delivery':
                return $deliveryCost;

            case 'percentage':
                $discount = $baseAmount * ($promotion->discount_value / 100);

                if ($promotion->max_discount_amount) {
                    $discount = min($discount, $promotion->max_discount_amount);
                }

                return round($discount, 2);

            case 'fixed_amount':
                return min($promotion->discount_value, $baseAmount);

            default:
                return 0;
        }
    }

    /**
     * @param  mixed  $order
     */
    public function applyPromotion($order, Promotion $promotion): array
    {
        $subtotal = $order->subtotal ?? 0;
        $deliveryCost = $order->delivery_cost ?? 0;

        $discountAmount = $this->calculateDiscount($promotion, $subtotal, $deliveryCost);

        if ($promotion->applies_to === 'delivery') {
            $order->delivery_discount = $discountAmount;
            $order->delivery_cost = max(0, $deliveryCost - $discountAmount);
        } else {
            $order->discount = $discountAmount;
            $order->subtotal = max(0, $subtotal - $discountAmount);
        }

        $order->promotion_id = $promotion->id;
        $order->save();

        if ($promotion->application_type === 'coupon') {
            $promotion->increment('times_used');
        }

        PromotionApplication::create([
            'tenant_id' => $promotion->tenant_id ?? $order->tenant_id,
            'promotion_id' => $promotion->id,
            'applicable_type' => get_class($order),
            'applicable_id' => $order->id,
            'original_amount' => $promotion->applies_to === 'delivery' ? $deliveryCost : $subtotal,
            'discount_amount' => $discountAmount,
            'applied_to' => $promotion->applies_to,
            'applied_at' => now(),
        ]);

        return [
            'discount_amount' => $discountAmount,
            'applies_to' => $promotion->applies_to,
            'promotion' => $promotion,
        ];
    }

    protected function appliesToService(Promotion $promotion, string $serviceType): bool
    {
        return $promotion->service_type === 'both' || $promotion->service_type === $serviceType;
    }

    /**
     * @param  non-empty-string|null  $branchId
     */
    protected function applyScopeQuery($query, ?string $branchId, ?int $customerId = null): void
    {
        $branch = $branchId
            ? Branch::withoutGlobalScopes()->with(['countryModel.region'])->find($branchId)
            : null;

        $query->where(function ($q) use ($branchId, $branch, $customerId) {
            $q->where('scope_type', 'all')
                ->orWhere(function ($sq) use ($branchId) {
                    $sq->where('scope_type', 'branches')
                        ->whereHas('branches', function ($ssq) use ($branchId) {
                            $ssq->where('branches.id', $branchId);
                        });
                });

            if ($branch) {
                if ($branch->country_id) {
                    $q->orWhere(function ($sq) use ($branch) {
                        $sq->where('scope_type', 'country')
                            ->where('country_id', $branch->country_id);
                    });
                }

                $regionId = $branch->countryModel?->region_id;
                if ($regionId) {
                    $q->orWhere(function ($sq) use ($regionId) {
                        $sq->where('scope_type', 'region')
                            ->where('region_id', $regionId);
                    });
                }
            }

            if ($customerId) {
                $q->orWhere(function ($sq) use ($customerId) {
                    $sq->where('scope_type', 'customers')
                        ->whereHas('customers', function ($csq) use ($customerId) {
                            $csq->where('customers.id', $customerId);
                        });
                });
            }
        });
    }
}
