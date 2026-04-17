import CouponInput from '@/components/CouponInput';
import { usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CreditCard,
    DollarSign,
    FileText,
    Gift,
    Mail,
    MessageSquare,
    Package,
    Phone,
    Printer,
    TrendingDown,
    User,
} from 'lucide-react';
import { useState } from 'react';

const Step4Confirmation = ({ data, onUpdate, onBack, onNext, config }) => {
    const { auth } = usePage().props;
    const [customer, setCustomer] = useState(
        data.customer || {
            name: '',
            email: '',
            phone: '',
            notes: '',
        },
    );

    const [paymentMethod, setPaymentMethod] = useState('card');
    const [isSubmitting, setIsSubmitting] = useState(false);

    // 🎯 Estado para cupón de descuento general (al final)
    const [appliedGeneralCoupon, setAppliedGeneralCoupon] = useState(
        data.generalPromotion || null,
    );

    const handleChange = (field, value) => {
        const updated = { ...customer, [field]: value };
        setCustomer(updated);
    };

    // 🎯 Manejar cupón general aplicado
    const handleGeneralCouponApplied = (promotion) => {
        setAppliedGeneralCoupon(promotion);
    };

    // 🎯 Manejar cupón general removido
    const handleGeneralCouponRemoved = () => {
        setAppliedGeneralCoupon(null);
    };

    const handleContinue = () => {
        onUpdate({
            customer,
            paymentMethod,
            generalPromotion: appliedGeneralCoupon, // 🎯 Guardar cupón general
            deliveryCost: finalDeliveryCost,
            finalTotal: grandTotal,
        });
        onNext();
    };

    const canSubmit = customer.name && customer.email;
    const totalPages = data.totalPages || 0;

    // 🎯 CÁLCULO DE PRECIOS PASO A PASO

    // 1. Subtotal de impresión (ya incluye descuentos de Step2 si los había)
    const printingSubtotal = data.priceBreakdown?.total || 0;

    // 2. Costo de delivery
    let deliveryCost =
        data.delivery?.method === 'delivery' ? config.delivery.base_cost : 0;

    // 3. Descuento en delivery (de Step3)
    let deliveryDiscount = 0;
    if (
        data.deliveryPromotion &&
        data.deliveryPromotion.applies_to === 'delivery'
    ) {
        const promo = data.deliveryPromotion;
        if (promo.discount_type === 'free_delivery') {
            deliveryDiscount = deliveryCost;
        } else if (promo.discount_type === 'percentage') {
            deliveryDiscount = deliveryCost * (promo.discount_value / 100);
        } else if (promo.discount_type === 'fixed_amount') {
            deliveryDiscount = Math.min(promo.discount_value, deliveryCost);
        }
    }

    // Envío gratis por monto mínimo
    const isFreeDeliveryByMinimum =
        printingSubtotal >= config.delivery.free_delivery_minimum;
    if (isFreeDeliveryByMinimum && deliveryDiscount < deliveryCost) {
        deliveryDiscount = deliveryCost;
    }

    const finalDeliveryCost = Math.max(0, deliveryCost - deliveryDiscount);

    // 4. Total antes del descuento general
    const totalBeforeGeneralDiscount = printingSubtotal + finalDeliveryCost;

    // 5. 🎯 DESCUENTO GENERAL (se aplica al total)
    let generalDiscount = 0;
    if (appliedGeneralCoupon) {
        const promo = appliedGeneralCoupon;
        if (promo.discount_type === 'percentage') {
            generalDiscount =
                totalBeforeGeneralDiscount * (promo.discount_value / 100);
            if (promo.max_discount_amount) {
                generalDiscount = Math.min(
                    generalDiscount,
                    promo.max_discount_amount,
                );
            }
        } else if (promo.discount_type === 'fixed_amount') {
            generalDiscount = Math.min(
                promo.discount_value,
                totalBeforeGeneralDiscount,
            );
        }
    }

    // 6. TOTAL FINAL
    const grandTotal = Math.max(
        0,
        totalBeforeGeneralDiscount - generalDiscount,
    );

    return (
        <div>
            <div className="mb-6">
                <div className="mb-2 flex items-center gap-2">
                    <motion.div
                        animate={{ rotate: [0, 5, -5, 0], scale: [1, 1.1, 1] }}
                        transition={{
                            duration: 2,
                            repeat: Infinity,
                            repeatDelay: 3,
                        }}
                        className="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-black-500/30"
                    >
                        <CheckCircle2 className="h-5 w-5 text-white" />
                    </motion.div>
                    <div>
                        <h2 className="text-2xl font-bold text-black-900">
                            Confirmar Pedido
                        </h2>
                        <p className="text-sm text-black-600">
                            Revisa y completa tu información
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div className="space-y-5">
                    <div>
                        <h3 className="mb-3 flex items-center gap-2 text-base font-bold text-black-900">
                            <User className="h-4 w-4 text-black-600" />
                            Información de Contacto
                        </h3>
                        <div className="space-y-3">
                            <div>
                                <label className="mb-2 block text-sm font-bold text-black-700">
                                    Nombre Completo *
                                </label>
                                <input
                                    type="text"
                                    value={customer.name}
                                    onChange={(e) =>
                                        handleChange('name', e.target.value)
                                    }
                                    placeholder="Juan Pérez"
                                    className="w-full rounded-xl border-2 border-black-200 p-3 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label className="mb-2 block text-sm font-bold text-black-700">
                                    Correo Electrónico *
                                </label>
                                <div className="relative">
                                    <Mail className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-black-600" />
                                    <input
                                        type="email"
                                        value={customer.email}
                                        onChange={(e) =>
                                            handleChange(
                                                'email',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="juan@ejemplo.com"
                                        className="w-full rounded-xl border-2 border-black-200 py-3 pr-3 pl-10 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="mb-2 block text-sm font-bold text-black-700">
                                    Teléfono (opcional)
                                </label>
                                <div className="relative">
                                    <Phone className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-black-600" />
                                    <input
                                        type="tel"
                                        value={customer.phone}
                                        onChange={(e) =>
                                            handleChange(
                                                'phone',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="2222-2222 o 7777-7777"
                                        className="w-full rounded-xl border-2 border-black-200 py-3 pr-3 pl-10 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="mb-2 block text-sm font-bold text-black-700">
                                    Notas Adicionales (opcional)
                                </label>
                                <div className="relative">
                                    <MessageSquare className="absolute top-3 left-3 h-4 w-4 text-black-600" />
                                    <textarea
                                        value={customer.notes}
                                        onChange={(e) =>
                                            handleChange(
                                                'notes',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Alguna instrucción especial..."
                                        className="w-full resize-none rounded-xl border-2 border-black-200 py-3 pr-3 pl-10 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                        rows="2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 className="mb-3 flex items-center gap-2 text-base font-bold text-black-900">
                            <CreditCard className="h-4 w-4 text-black-600" />
                            Método de Pago
                        </h3>
                        <div className="grid grid-cols-1 gap-2">
                            {[
                                {
                                    value: 'card',
                                    label: 'Tarjeta',
                                    icon: CreditCard,
                                    desc: 'Débito o crédito',
                                },
                            ].map((method) => (
                                <motion.button
                                    key={method.value}
                                    whileHover={{ scale: 1.02 }}
                                    whileTap={{ scale: 0.98 }}
                                    onClick={() =>
                                        setPaymentMethod(method.value)
                                    }
                                    className={`rounded-xl border-2 p-3 text-left transition-all ${paymentMethod === method.value ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-lg shadow-black-500/20' : 'border-black-200 bg-white hover:border-black-300'}`}
                                >
                                    <div className="flex items-center gap-3">
                                        <div
                                            className={`flex h-8 w-8 items-center justify-center rounded-lg ${paymentMethod === method.value ? 'bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2' : 'bg-black-100'}`}
                                        >
                                            <method.icon
                                                className={`h-4 w-4 ${paymentMethod === method.value ? 'text-white' : 'text-black-600'}`}
                                            />
                                        </div>
                                        <div className="flex-1">
                                            <div className="text-sm font-bold text-black-900">
                                                {method.label}
                                            </div>
                                            <div className="text-xs text-black-600">
                                                {method.desc}
                                            </div>
                                        </div>
                                        {paymentMethod === method.value && (
                                            <CheckCircle2 className="h-5 w-5 text-black-600" />
                                        )}
                                    </div>
                                </motion.button>
                            ))}
                        </div>
                    </div>
                </div>

                <div>
                    <div className="sticky top-6 rounded-2xl border-2 border-black-200 bg-linear-to-br from-black-50 to-black-100 p-5 shadow-lg">
                        <div className="mb-4 flex items-center gap-2">
                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-md">
                                <FileText className="h-4 w-4 text-white" />
                            </div>
                            <h3 className="text-base font-bold text-black-900">
                                Resumen del Pedido
                            </h3>
                        </div>

                        {/* 🎯 MOSTRAR CUPÓN DE DELIVERY SI HAY */}
                        {data.deliveryPromotion && (
                            <div className="mb-4 rounded-xl border-2 border-green-300 bg-linear-to-br from-green-50 to-emerald-50 p-3">
                                <div className="mb-1 flex items-center gap-2">
                                    <Gift className="h-4 w-4 text-green-600" />
                                    <h4 className="text-sm font-bold text-green-900">
                                        Promoción de Envío
                                    </h4>
                                </div>
                                <div className="flex items-center gap-2 text-xs">
                                    <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                    <span className="flex-1 font-medium text-green-700">
                                        {data.deliveryPromotion.name}
                                    </span>
                                    <span className="font-bold text-green-700">
                                        {data.deliveryPromotion.label ||
                                            data.deliveryPromotion
                                                .discount_label}
                                    </span>
                                </div>
                            </div>
                        )}

                        <div className="mb-4 rounded-xl border border-black-200 bg-white p-3">
                            <div className="mb-2 flex items-center gap-2">
                                <FileText className="h-4 w-4 text-black-600" />
                                <h4 className="text-sm font-bold text-black-900">
                                    Archivos
                                </h4>
                            </div>
                            <div className="space-y-1 text-xs">
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Documentos:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {data.files?.length || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Total páginas:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {totalPages}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="mb-4 rounded-xl border border-black-200 bg-white p-3">
                            <div className="mb-2 flex items-center gap-2">
                                <Printer className="h-4 w-4 text-black-600" />
                                <h4 className="text-sm font-bold text-black-900">
                                    Configuración
                                </h4>
                            </div>
                            <div className="space-y-1.5 text-xs">
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Tipo:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {data.config?.printType === 'bw'
                                            ? 'Blanco y Negro'
                                            : 'Color'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Tamaño:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {data.config?.paperSize?.toUpperCase() ||
                                            'LETTER'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Copias:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {data.config?.copies || 1}
                                    </span>
                                </div>
                                {data.config?.doubleSided && (
                                    <div className="flex justify-between">
                                        <span className="text-black-600">
                                            Doble cara:
                                        </span>
                                        <span className="font-bold text-blue-600">
                                            Sí
                                        </span>
                                    </div>
                                )}
                                {data.config?.binding && (
                                    <div className="flex justify-between">
                                        <span className="text-black-600">
                                            Engargolado:
                                        </span>
                                        <span className="font-bold text-black-600">
                                            Sí
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="mb-4 rounded-xl border border-black-200 bg-white p-3">
                            <div className="mb-2 flex items-center gap-2">
                                <Package className="h-4 w-4 text-black-600" />
                                <h4 className="text-sm font-bold text-black-900">
                                    Entrega
                                </h4>
                            </div>
                            <div className="text-xs">
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Método:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        {data.delivery?.method === 'pickup'
                                            ? 'Recoger en tienda'
                                            : 'Envío a domicilio'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* 🎯 CUPÓN DE DESCUENTO GENERAL */}
                        <div className="mb-4">
                            <CouponInput
                                onCouponApplied={handleGeneralCouponApplied}
                                onCouponRemoved={handleGeneralCouponRemoved}
                                currentCoupon={appliedGeneralCoupon}
                                orderData={{
                                    priceBreakdown: {
                                        total: totalBeforeGeneralDiscount,
                                    },
                                    delivery: data.delivery,
                                }}
                                storeId={
                                    data.delivery?.branch_id ||
                                    auth?.user?.branch_id
                                }
                                deliveryCost={finalDeliveryCost}
                                serviceType="print_order"
                                appliesTo="subtotal"
                            />
                        </div>

                        <div className="rounded-xl border-2 border-black-200 bg-linear-to-br from-black-50 via-slate-50 to-black-50 p-4">
                            <div className="mb-3 flex items-center gap-2">
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-md">
                                    <DollarSign className="h-4 w-4 text-white" />
                                </div>
                                <h4 className="text-sm font-bold text-black-900">
                                    Desglose de Costos
                                </h4>
                            </div>
                            <div className="space-y-2 text-xs">
                                <div className="flex justify-between rounded-lg bg-white/60 p-2 backdrop-blur-sm">
                                    <span className="text-black-600">
                                        Subtotal de impresión
                                    </span>
                                    <span className="font-bold text-black-900">
                                        ${printingSubtotal.toFixed(2)}
                                    </span>
                                </div>

                                {data.delivery?.method === 'delivery' && (
                                    <>
                                        {deliveryDiscount > 0 ? (
                                            <>
                                                <div className="flex justify-between rounded-lg bg-white/60 p-2 backdrop-blur-sm">
                                                    <span className="text-black-600">
                                                        Envío base
                                                    </span>
                                                    <span className="font-bold text-gray-500 line-through">
                                                        $
                                                        {deliveryCost.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                </div>
                                                <div className="flex justify-between rounded-lg bg-green-50 p-2">
                                                    <span className="flex items-center gap-1.5 font-medium text-green-700">
                                                        <Gift className="h-3.5 w-3.5" />
                                                        {isFreeDeliveryByMinimum
                                                            ? 'Envío gratis'
                                                            : 'Descuento envío'}
                                                    </span>
                                                    <span className="font-bold text-green-700">
                                                        -$
                                                        {deliveryDiscount.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                </div>
                                                {finalDeliveryCost > 0 && (
                                                    <div className="flex justify-between rounded-lg bg-white/60 p-2 backdrop-blur-sm">
                                                        <span className="text-black-600">
                                                            Envío final
                                                        </span>
                                                        <span className="font-bold text-black-900">
                                                            $
                                                            {finalDeliveryCost.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </div>
                                                )}
                                            </>
                                        ) : (
                                            <div className="flex justify-between rounded-lg bg-white/60 p-2 backdrop-blur-sm">
                                                <span className="text-black-600">
                                                    Envío
                                                </span>
                                                <span className="font-bold text-black-900">
                                                    $
                                                    {finalDeliveryCost.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </div>
                                        )}
                                    </>
                                )}

                                <div className="flex justify-between border-t-2 border-black-200 pt-2">
                                    <span className="font-semibold text-black-700">
                                        Subtotal
                                    </span>
                                    <span className="font-bold text-black-900">
                                        ${totalBeforeGeneralDiscount.toFixed(2)}
                                    </span>
                                </div>

                                {/* 🎯 DESCUENTO GENERAL */}
                                {generalDiscount > 0 && (
                                    <motion.div
                                        initial={{ scale: 0.9, opacity: 0 }}
                                        animate={{ scale: 1, opacity: 1 }}
                                        className="flex justify-between rounded-lg border border-green-200 bg-linear-to-br from-green-50 to-emerald-50 p-2"
                                    >
                                        <span className="flex items-center gap-1.5 font-medium text-green-700">
                                            <TrendingDown className="h-3.5 w-3.5" />
                                            Descuento -{' '}
                                            {appliedGeneralCoupon?.name}
                                        </span>
                                        <span className="font-bold text-green-700">
                                            -${generalDiscount.toFixed(2)}
                                        </span>
                                    </motion.div>
                                )}

                                <div className="flex justify-between border-t-2 border-black-300 pt-3">
                                    <span className="text-base font-bold text-black-900">
                                        TOTAL
                                    </span>
                                    <span className="text-xl font-bold text-black-600">
                                        ${grandTotal.toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="mt-4 rounded-xl border border-black-200 bg-white p-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-black-600">
                                    Método de pago:
                                </span>
                                <span className="flex items-center gap-1.5 text-xs font-bold text-black-900">
                                    {paymentMethod === 'card' && (
                                        <>
                                            <CreditCard className="h-4 w-4 text-black-600" />
                                            Tarjeta
                                        </>
                                    )}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 rounded-xl border-2 border-blue-200 bg-linear-to-br from-blue-50 to-cyan-50 p-3">
                        <p className="flex items-center gap-2 text-xs text-black-700">
                            <Mail className="h-4 w-4 shrink-0 text-blue-600" />
                            <span>
                                <strong>Recibirás un correo</strong> con los
                                detalles de tu pedido.
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div className="mt-6 flex justify-between border-t-2 border-black-100 pt-6">
                <motion.button
                    whileHover={{ scale: 1.05, x: -5 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={onBack}
                    disabled={isSubmitting}
                    className="flex items-center gap-2 rounded-xl border-2 border-black-200 px-6 py-3 text-sm font-bold text-black-700 transition-all hover:border-black-300 hover:bg-black-100 disabled:opacity-50"
                >
                    <ChevronLeft className="h-4 w-4" />
                    Atrás
                </motion.button>
                <motion.button
                    whileHover={{
                        scale: canSubmit && !isSubmitting ? 1.05 : 1,
                        x: canSubmit && !isSubmitting ? 5 : 0,
                    }}
                    whileTap={{ scale: canSubmit && !isSubmitting ? 0.95 : 1 }}
                    disabled={!canSubmit || isSubmitting}
                    onClick={handleContinue}
                    className={`flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold transition-all ${canSubmit ? 'bg-linear-to-r from-mbe-accent to-mbe-accent-2 text-white shadow-xl shadow-black-500/30 hover:from-mbe-accent-2 hover:to-mbe-accent hover:shadow-2xl hover:shadow-black-500/40' : 'cursor-not-allowed bg-black-200 text-black-400'}`}
                >
                    {isSubmitting ? (
                        <>
                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                            Procesando...
                        </>
                    ) : (
                        <>
                            Ir al pago
                            {canSubmit && (
                                <motion.div
                                    animate={{ x: [0, 5, 0] }}
                                    transition={{
                                        duration: 1,
                                        repeat: Infinity,
                                    }}
                                >
                                    <ChevronRight className="h-5 w-5" />
                                </motion.div>
                            )}
                        </>
                    )}
                </motion.button>
            </div>
        </div>
    );
};

export default Step4Confirmation;
