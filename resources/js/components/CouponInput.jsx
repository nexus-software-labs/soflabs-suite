import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from 'axios';
import { Gift, Loader2, X } from 'lucide-react';
import { useState } from 'react';

function getCsrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

/**
 * Validación de cupón vía API para el flujo de impresión.
 *
 * @param {{
 *   onCouponApplied: (promotion: Record<string, unknown>) => void,
 *   onCouponRemoved: () => void,
 *   currentCoupon: Record<string, unknown> | null,
 *   orderData: { priceBreakdown?: { total?: number }, delivery?: Record<string, unknown> },
 *   storeId?: string | null,
 *   serviceType: string,
 *   appliesTo?: string,
 *   deliveryCost?: number,
 * }} props
 */
export default function CouponInput({
    onCouponApplied,
    onCouponRemoved,
    currentCoupon,
    orderData,
    storeId,
    serviceType,
    appliesTo: _appliesTo,
    deliveryCost = 0,
}) {
    const [code, setCode] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const subtotal = Number(orderData?.priceBreakdown?.total ?? 0) || 0;
    const delivery = Number(deliveryCost) || 0;

    const apply = async () => {
        setError(null);
        const trimmed = code.trim();
        if (!trimmed) {
            setError('Ingresá un código de cupón.');
            return;
        }
        if (!storeId) {
            setError(
                'Seleccioná una sucursal de recogida o envío antes de aplicar el cupón.',
            );
            return;
        }

        setLoading(true);
        try {
            const token = getCsrfToken();
            const response = await axios.post(
                '/api/promotions/validate-coupon',
                {
                    coupon_code: trimmed,
                    branch_id: storeId,
                    service_type: serviceType,
                    subtotal,
                    delivery_cost: delivery,
                },
                {
                    headers: {
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                        Accept: 'application/json',
                    },
                },
            );

            if (response.data?.success && response.data?.data) {
                onCouponApplied?.(response.data.data);
                setCode('');
            } else {
                setError(
                    response.data?.message ||
                        'No se pudo aplicar el cupón. Intentá de nuevo.',
                );
            }
        } catch (e) {
            const msg =
                e?.response?.data?.message ||
                e?.message ||
                'Cupón inválido o no disponible.';
            setError(msg);
        } finally {
            setLoading(false);
        }
    };

    const remove = () => {
        setError(null);
        onCouponRemoved?.();
    };

    if (currentCoupon) {
        return (
            <div className="flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3">
                <div className="flex min-w-0 items-start gap-2">
                    <Gift
                        className="mt-0.5 h-4 w-4 shrink-0 text-emerald-700"
                        aria-hidden
                    />
                    <div className="min-w-0">
                        <p className="text-xs font-bold text-emerald-900">
                            Cupón aplicado
                        </p>
                        <p className="truncate text-sm font-semibold text-emerald-950">
                            {currentCoupon.name}
                        </p>
                        {currentCoupon.coupon_code ? (
                            <p className="text-xs text-emerald-800">
                                Código: {currentCoupon.coupon_code}
                            </p>
                        ) : null}
                    </div>
                </div>
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    className="shrink-0"
                    onClick={remove}
                >
                    <X className="h-4 w-4" aria-hidden />
                    Quitar
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-2 rounded-xl border border-black-200 bg-white p-3">
            <div className="flex items-center gap-2">
                <Gift className="h-4 w-4 text-black-600" aria-hidden />
                <p className="text-sm font-bold text-black-900">
                    Cupón de descuento
                </p>
            </div>
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                <Input
                    value={code}
                    onChange={(e) => setCode(e.target.value)}
                    placeholder="Código"
                    className="sm:flex-1"
                    disabled={loading}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            apply();
                        }
                    }}
                />
                <Button
                    type="button"
                    onClick={apply}
                    disabled={loading}
                    className="sm:w-32"
                >
                    {loading ? (
                        <Loader2 className="h-4 w-4 animate-spin" aria-hidden />
                    ) : (
                        'Aplicar'
                    )}
                </Button>
            </div>
            {error ? (
                <p className="text-xs font-medium text-red-600">{error}</p>
            ) : null}
        </div>
    );
}
