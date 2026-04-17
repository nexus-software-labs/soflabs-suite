import { Gift, Sparkles } from 'lucide-react';

/**
 * Banner compacto para promociones automáticas del flujo de impresión.
 *
 * @param {{ promotion: Record<string, unknown> }} props
 */
export default function PromotionBanner({ promotion }) {
    if (!promotion) {
        return null;
    }

    const title =
        typeof promotion.name === 'string' ? promotion.name : 'Promoción';
    const description =
        typeof promotion.description === 'string'
            ? promotion.description
            : '';
    const label =
        typeof promotion.label === 'string' ? promotion.label : '';

    return (
        <div className="rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-emerald-950 shadow-sm">
            <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                    <Gift className="h-5 w-5" aria-hidden />
                </div>
                <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                        <Sparkles
                            className="h-4 w-4 shrink-0 text-emerald-700"
                            aria-hidden
                        />
                        <h3 className="text-sm font-bold">{title}</h3>
                    </div>
                    {description ? (
                        <p className="mt-1 text-xs text-emerald-900/90">
                            {description}
                        </p>
                    ) : null}
                    {label ? (
                        <p className="mt-2 text-xs font-semibold text-emerald-800">
                            {label}
                        </p>
                    ) : null}
                </div>
            </div>
        </div>
    );
}
