import { CheckCircle2, MapPin, Pencil, Plus } from 'lucide-react';

/**
 * Lista de direcciones del cliente para entrega a domicilio.
 *
 * @param {{
 *   addresses: Array<Record<string, unknown>>,
 *   selectedAddressId: string | number | null,
 *   onSelectAddress: (id: string | number | null) => void,
 *   selectable?: boolean,
 *   title?: string,
 *   icon?: import('lucide-react').LucideIcon,
 *   showCreateButton?: boolean,
 *   showEditButton?: boolean,
 *   showDeleteButton?: boolean,
 * }} props
 */
export default function CustomerAddressSelector({
    addresses = [],
    selectedAddressId,
    onSelectAddress,
    selectable = true,
    title = 'Direcciones',
    icon: Icon = MapPin,
    showCreateButton = false,
    showEditButton = false,
    showDeleteButton: _showDeleteButton = false,
}) {
    const formatLine = (addr) => {
        const parts = [
            addr.address,
            addr.locality,
            addr.city,
            addr.region || addr.state,
            addr.country,
        ].filter(Boolean);

        return parts.length ? parts.join(', ') : 'Sin detalle de dirección';
    };

    return (
        <div className="space-y-3">
            <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    <Icon className="h-5 w-5 text-black-600" aria-hidden />
                    <h3 className="text-base font-bold text-black-900">{title}</h3>
                </div>
                <div className="flex flex-wrap gap-2">
                    {showCreateButton ? (
                        <button
                            type="button"
                            className="inline-flex items-center gap-1 rounded-lg border border-black-200 bg-white px-2 py-1 text-xs font-semibold text-black-800 shadow-sm hover:bg-black-50"
                            onClick={() => {
                                /* enlazar a gestión de direcciones cuando exista la ruta */
                            }}
                        >
                            <Plus className="h-3.5 w-3.5" aria-hidden />
                            Nueva
                        </button>
                    ) : null}
                </div>
            </div>

            {addresses.length === 0 ? (
                <p className="rounded-xl border border-dashed border-black-200 bg-black-50/50 p-4 text-sm text-black-600">
                    No tenés direcciones guardadas. Podés agregar una desde tu
                    perfil o completar la dirección manualmente en el formulario.
                </p>
            ) : (
                <div className="space-y-2">
                    {addresses.map((addr) => {
                        const id = addr.id;
                        const isSelected =
                            id !== undefined &&
                            id !== null &&
                            String(selectedAddressId) === String(id);

                        return (
                            <button
                                key={String(id)}
                                type="button"
                                disabled={!selectable}
                                onClick={() =>
                                    selectable && onSelectAddress?.(id)
                                }
                                className={`relative w-full rounded-2xl border-2 p-4 text-left transition-all ${
                                    isSelected
                                        ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-md'
                                        : 'border-black-200 bg-white shadow-sm hover:border-black-300'
                                } ${!selectable ? 'cursor-not-allowed opacity-60' : ''}`}
                            >
                                <div className="flex items-start gap-3">
                                    <div
                                        className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${
                                            isSelected
                                                ? 'bg-mbe-secondary-2 text-white'
                                                : 'bg-black-100 text-black-600'
                                        }`}
                                    >
                                        <MapPin className="h-4 w-4" aria-hidden />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-sm font-bold text-black-900">
                                                    {addr.name || 'Dirección'}
                                                </p>
                                                <p className="mt-0.5 text-xs text-black-600">
                                                    {formatLine(addr)}
                                                </p>
                                                {addr.phone ? (
                                                    <p className="mt-1 text-xs text-black-500">
                                                        Tel. {addr.phone}
                                                    </p>
                                                ) : null}
                                            </div>
                                            {showEditButton ? (
                                                <span
                                                    className="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-black-500"
                                                    onClick={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                    onKeyDown={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                    role="presentation"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>
                                    {isSelected ? (
                                        <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mbe-accent text-white">
                                            <CheckCircle2
                                                className="h-4 w-4"
                                                aria-hidden
                                            />
                                        </div>
                                    ) : null}
                                </div>
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
