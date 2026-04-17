import CustomerAddressSelector from '@/components/CustomerAddressSelector';
import axios from 'axios';
import { AnimatePresence, motion } from 'framer-motion';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Gift,
    Loader2,
    MapPin,
    Package,
    Phone,
    Sparkles,
    Truck,
} from 'lucide-react';
import { useEffect, useState } from 'react';

const Step3Delivery = ({
    data,
    onUpdate,
    onNext,
    onBack,
    branches = [],
    customerAddresses = [],
    config,
    auth,
}) => {
    const [delivery, setDelivery] = useState(
        data.delivery || {
            method: 'pickup',
            branch_id: null,
            customerAddressId: null,
            address: '',
            phone: '',
            notes: '',
        },
    );

    // 🎯 Estado para promoción automática de delivery
    const [automaticDeliveryPromotion, setAutomaticDeliveryPromotion] =
        useState(data.deliveryPromotion || null);
    const [isLoadingPromotion, setIsLoadingPromotion] = useState(false);

    const handleChange = (field, value) => {
        const updated = { ...delivery, [field]: value };
        setDelivery(updated);
    };

    // 🎯 Cargar promoción automática cuando se selecciona delivery
    useEffect(() => {
        if (
            delivery.method === 'delivery' &&
            delivery.branch_id &&
            data.priceBreakdown
        ) {
            loadAutomaticPromotion();
        } else {
            setAutomaticDeliveryPromotion(null);
        }
    }, [delivery.method, delivery.branch_id, data.priceBreakdown?.total]);

    const loadAutomaticPromotion = async () => {
        setIsLoadingPromotion(true);
        try {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            const response = await axios.post(
                '/api/promotions/best-promotion',
                {
                    branch_id: delivery.branch_id,
                    service_type: 'print_order',
                    subtotal: data.priceBreakdown?.total || 0,
                    delivery_cost: config.delivery.base_cost,
                    applies_to: 'delivery', // Solo promociones de delivery
                },
                {
                    headers: {
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                        Accept: 'application/json',
                    },
                },
            );

            if (response.data.success) {
                setAutomaticDeliveryPromotion(response.data.data);
            }
        } catch (error) {
            // No hay promociones disponibles, está bien
            setAutomaticDeliveryPromotion(null);
        } finally {
            setIsLoadingPromotion(false);
        }
    };

    const handleContinue = () => {
        onUpdate({
            delivery,
            deliveryPromotion: automaticDeliveryPromotion,
        });
        onNext();
    };

    const canContinue =
        delivery.method === 'pickup'
            ? delivery.branch_id !== null
            : delivery.customerAddressId !== null ||
              (delivery.address && delivery.phone);

    const originalDeliveryCost = config.delivery.base_cost;
    const printingSubtotal = data.priceBreakdown?.total || 0;

    // 🎯 Calcular costo final con promoción automática
    let finalDeliveryCost = originalDeliveryCost;
    let deliveryDiscount = 0;

    if (delivery.method === 'delivery') {
        // Promoción automática de delivery
        if (
            automaticDeliveryPromotion &&
            automaticDeliveryPromotion.applies_to === 'delivery'
        ) {
            if (automaticDeliveryPromotion.discount_type === 'free_delivery') {
                deliveryDiscount = originalDeliveryCost;
            } else if (
                automaticDeliveryPromotion.discount_type === 'percentage'
            ) {
                deliveryDiscount =
                    originalDeliveryCost *
                    (automaticDeliveryPromotion.discount_value / 100);
            } else if (
                automaticDeliveryPromotion.discount_type === 'fixed_amount'
            ) {
                deliveryDiscount = Math.min(
                    automaticDeliveryPromotion.discount_value,
                    originalDeliveryCost,
                );
            }
        }

        // Envío gratis por monto mínimo
        const isFreeByMinimum =
            printingSubtotal >= config.delivery.free_delivery_minimum;
        if (isFreeByMinimum && deliveryDiscount < originalDeliveryCost) {
            deliveryDiscount = originalDeliveryCost;
        }

        finalDeliveryCost = Math.max(
            0,
            originalDeliveryCost - deliveryDiscount,
        );
    }

    const hasFreeDelivery = deliveryDiscount >= originalDeliveryCost;
    const isFreeByMinimum =
        printingSubtotal >= config.delivery.free_delivery_minimum;

    return (
        <div>
            <div className="mb-6">
                <div className="mb-2 flex items-center gap-2">
                    <motion.div
                        animate={{
                            rotate: [0, 5, -5, 0],
                            scale: [1, 1.1, 1],
                        }}
                        transition={{
                            duration: 2,
                            repeat: Infinity,
                            repeatDelay: 3,
                        }}
                        className="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-black-500/30"
                    >
                        <Truck className="h-5 w-5 text-white" />
                    </motion.div>
                    <div>
                        <h2 className="text-2xl font-bold text-black-900">
                            Método de Entrega
                        </h2>
                        <p className="text-sm text-black-600">
                            Elige cómo quieres recibir tu pedido
                        </p>
                    </div>
                </div>
            </div>

            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <motion.button
                    whileHover={{ scale: 1.02, y: -4 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => handleChange('method', 'pickup')}
                    className={`relative overflow-hidden rounded-2xl border-2 p-5 text-left transition-all ${
                        delivery.method === 'pickup'
                            ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-2xl shadow-black-500/20'
                            : 'border-black-200 bg-white shadow-lg hover:border-black-300'
                    }`}
                >
                    {delivery.method === 'pickup' && (
                        <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            className="absolute top-3 right-3"
                        >
                            <div className="flex h-7 w-7 items-center justify-center rounded-full bg-mbe-accent">
                                <CheckCircle2 className="h-4 w-4 text-white" />
                            </div>
                        </motion.div>
                    )}
                    <div className="flex items-start gap-3">
                        <motion.div
                            animate={{
                                scale:
                                    delivery.method === 'pickup'
                                        ? [1, 1.1, 1]
                                        : 1,
                            }}
                            transition={{ duration: 0.5 }}
                            className={`rounded-xl p-3 transition-all ${
                                delivery.method === 'pickup'
                                    ? 'bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-black-500/30'
                                    : 'bg-black-100'
                            }`}
                        >
                            <Package
                                className={`h-6 w-6 ${
                                    delivery.method === 'pickup'
                                        ? 'text-white'
                                        : 'text-black-600'
                                }`}
                            />
                        </motion.div>
                        <div className="flex-1">
                            <h3 className="mb-1 text-base font-bold text-black-900">
                                Recoger en Tienda
                            </h3>
                            <p className="mb-2 text-xs text-black-600">
                                Recoge tu pedido en cualquiera de nuestras
                                ubicaciones
                            </p>
                            <div className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                <Sparkles className="h-3 w-3" />
                                Sin costo adicional
                            </div>
                        </div>
                    </div>
                </motion.button>

                <motion.button
                    whileHover={{ scale: 1.02, y: -4 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => handleChange('method', 'delivery')}
                    className={`relative overflow-hidden rounded-2xl border-2 p-5 text-left transition-all ${
                        delivery.method === 'delivery'
                            ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-2xl shadow-black-500/20'
                            : 'border-black-200 bg-white shadow-lg hover:border-black-300'
                    }`}
                >
                    {/* 🎯 Badge de promoción automática */}
                    {automaticDeliveryPromotion &&
                        delivery.method === 'delivery' && (
                            <motion.div
                                initial={{ scale: 0, rotate: -12 }}
                                animate={{ scale: 1, rotate: -12 }}
                                className="absolute -top-1 -right-1 z-10"
                            >
                                <div className="rounded-lg border-2 border-white bg-linear-to-br from-green-500 to-emerald-600 px-3 py-1.5 text-white shadow-lg">
                                    <div className="flex items-center gap-1">
                                        <Gift className="h-3.5 w-3.5" />
                                        <span className="text-xs font-bold">
                                            {
                                                automaticDeliveryPromotion.discount_label
                                            }
                                        </span>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                    {/* 🎯 Badge de envío gratis por monto mínimo */}
                    {isFreeByMinimum &&
                        delivery.method === 'delivery' &&
                        !automaticDeliveryPromotion && (
                            <motion.div
                                initial={{ scale: 0, rotate: -12 }}
                                animate={{ scale: 1, rotate: -12 }}
                                className="absolute -top-1 -right-1 z-10"
                            >
                                <div className="rounded-lg border-2 border-white bg-linear-to-br from-green-500 to-emerald-600 px-3 py-1.5 text-white shadow-lg">
                                    <div className="flex items-center gap-1">
                                        <Gift className="h-3.5 w-3.5" />
                                        <span className="text-xs font-bold">
                                            ¡GRATIS!
                                        </span>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                    {delivery.method === 'delivery' && (
                        <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            className="absolute top-3 right-3"
                        >
                            <div className="flex h-7 w-7 items-center justify-center rounded-full bg-mbe-accent">
                                <CheckCircle2 className="h-4 w-4 text-white" />
                            </div>
                        </motion.div>
                    )}

                    <div className="flex items-start gap-3">
                        <motion.div
                            animate={{
                                scale:
                                    delivery.method === 'delivery'
                                        ? [1, 1.1, 1]
                                        : 1,
                            }}
                            transition={{ duration: 0.5 }}
                            className={`rounded-xl p-3 transition-all ${
                                delivery.method === 'delivery'
                                    ? 'bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-black-500/30'
                                    : 'bg-black-100'
                            }`}
                        >
                            <Truck
                                className={`h-6 w-6 ${
                                    delivery.method === 'delivery'
                                        ? 'text-white'
                                        : 'text-black-600'
                                }`}
                            />
                        </motion.div>
                        <div className="flex-1">
                            <h3 className="mb-1 text-base font-bold text-black-900">
                                Envío a Domicilio
                            </h3>
                            <p className="mb-2 text-xs text-black-600">
                                Recibe tu pedido en la puerta de tu casa u
                                oficina
                            </p>

                            {isLoadingPromotion ? (
                                <div className="inline-flex items-center gap-1.5 rounded-full bg-black-100 px-2.5 py-1 text-xs font-bold text-black-600">
                                    <Loader2 className="h-3 w-3 animate-spin" />
                                    Verificando...
                                </div>
                            ) : hasFreeDelivery ? (
                                <div className="flex items-center gap-2">
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-black-200 px-2.5 py-1 text-xs font-bold text-black-500 line-through">
                                        ${originalDeliveryCost.toFixed(2)}
                                    </div>
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                        <Sparkles className="h-3 w-3" />
                                        ¡GRATIS!
                                    </div>
                                </div>
                            ) : deliveryDiscount > 0 ? (
                                <div className="flex items-center gap-2">
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-black-200 px-2.5 py-1 text-xs font-bold text-black-500 line-through">
                                        ${originalDeliveryCost.toFixed(2)}
                                    </div>
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                        <Sparkles className="h-3 w-3" />$
                                        {finalDeliveryCost.toFixed(2)}
                                    </div>
                                </div>
                            ) : (
                                <div className="inline-flex items-center gap-1.5 rounded-full bg-black-100 px-2.5 py-1 text-xs font-bold text-black-700">
                                    <Truck className="h-3 w-3" />
                                    Desde ${originalDeliveryCost.toFixed(2)}
                                </div>
                            )}

                            {/* 🎯 Mensaje de promoción */}
                            {automaticDeliveryPromotion && (
                                <motion.p
                                    initial={{ opacity: 0, y: -10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="mt-2 text-xs font-medium text-green-700"
                                >
                                    🎉 {automaticDeliveryPromotion.description}
                                </motion.p>
                            )}

                            {isFreeByMinimum && !automaticDeliveryPromotion && (
                                <motion.p
                                    initial={{ opacity: 0, y: -10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="mt-2 text-xs font-medium text-green-700"
                                >
                                    🎉 Envío gratis por compra mayor a $
                                    {config.delivery.free_delivery_minimum.toFixed(
                                        2,
                                    )}
                                </motion.p>
                            )}
                        </div>
                    </div>
                </motion.button>
            </div>

            <AnimatePresence mode="wait">
                {delivery.method === 'pickup' && (
                    <motion.div
                        key="pickup"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -20 }}
                        transition={{ duration: 0.3 }}
                    >
                        <div className="mb-3 flex items-center gap-2">
                            <MapPin className="h-5 w-5 text-black-600" />
                            <h3 className="text-base font-bold text-black-900">
                                Selecciona una tienda
                            </h3>
                        </div>
                        <div className="space-y-3">
                            {branches?.map((branch, index) => (
                                <motion.button
                                    key={branch.id}
                                    initial={{ opacity: 0, x: -20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: index * 0.1 }}
                                    whileHover={{ scale: 1.01, x: 5 }}
                                    whileTap={{ scale: 0.99 }}
                                    onClick={() =>
                                        handleChange('branch_id', branch.id)
                                    }
                                    className={`relative w-full rounded-2xl border-2 p-4 text-left transition-all ${
                                        delivery.branch_id === branch.id
                                            ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-lg shadow-black-500/20'
                                            : 'border-black-200 bg-white shadow-md hover:border-black-300'
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        <motion.div
                                            animate={{
                                                scale:
                                                    delivery.branch_id ===
                                                    branch.id
                                                        ? [1, 1.1, 1]
                                                        : 1,
                                            }}
                                            transition={{ duration: 0.5 }}
                                            className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all ${
                                                delivery.branch_id === branch.id
                                                    ? 'bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-black-500/30'
                                                    : 'bg-black-100'
                                            }`}
                                        >
                                            <MapPin
                                                className={`h-5 w-5 ${
                                                    delivery.branch_id ===
                                                    branch.id
                                                        ? 'text-white'
                                                        : 'text-black-600'
                                                }`}
                                            />
                                        </motion.div>
                                        <div className="flex-1">
                                            <h4 className="mb-1 text-base font-bold text-black-900">
                                                {branch.name}
                                            </h4>
                                            <p className="mb-2 text-xs text-black-600">
                                                {branch.address}, {branch.city}
                                            </p>
                                            <div className="flex flex-wrap gap-1.5">
                                                {branch.city && branch.country && (
                                                    <span className="inline-flex items-center gap-1 rounded-full border border-black-200 bg-white/80 px-2 py-1 text-xs font-medium backdrop-blur-sm">
                                                        <MapPin className="h-3 w-3 text-black-600" />
                                                        {branch.city},{' '}
                                                        {branch.country}
                                                    </span>
                                                )}
                                                {branch.phone && (
                                                    <span className="inline-flex items-center gap-1 rounded-full border border-black-200 bg-white/80 px-2 py-1 text-xs font-medium backdrop-blur-sm">
                                                        <Phone className="h-3 w-3 text-black-600" />
                                                        {branch.phone}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        {delivery.branch_id === branch.id && (
                                            <motion.div
                                                initial={{ scale: 0 }}
                                                animate={{ scale: 1 }}
                                                className="shrink-0"
                                            >
                                                <div className="flex h-7 w-7 items-center justify-center rounded-full bg-mbe-accent shadow-lg">
                                                    <CheckCircle2 className="h-4 w-4 text-white" />
                                                </div>
                                            </motion.div>
                                        )}
                                    </div>
                                </motion.button>
                            ))}
                        </div>
                    </motion.div>
                )}

                {delivery.method === 'delivery' && (
                    <motion.div
                        key="delivery"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -20 }}
                        transition={{ duration: 0.3 }}
                        className="space-y-4"
                    >
                        {/* 🎯 VALIDACIÓN: Solo mostrar selector si hay usuario autenticado */}
                        {auth?.user ? (
                            <CustomerAddressSelector
                                addresses={customerAddresses}
                                selectedAddressId={delivery.customerAddressId}
                                onSelectAddress={(addressId) =>
                                    handleChange('customerAddressId', addressId)
                                }
                                selectable={true}
                                title="Selecciona una dirección de entrega"
                                icon={Truck}
                                showCreateButton={true}
                                showEditButton={true}
                                showDeleteButton={false}
                            />
                        ) : (
                            <div className="">
                                <div className="mb-3 flex items-center gap-2">
                                    <Truck className="h-5 w-5 text-black-600" />
                                    <h3 className="text-base font-bold text-black-900">
                                        Información de Envío
                                    </h3>
                                </div>
                                <div>
                                    <label className="mb-2 block text-sm font-bold text-black-700">
                                        Dirección de Entrega *
                                    </label>
                                    <textarea
                                        value={delivery.address}
                                        onChange={(e) =>
                                            handleChange(
                                                'address',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Calle, número, colonia, municipio, referencias (ej: portón azul)..."
                                        className="w-full resize-none rounded-xl border-2 border-black-200 p-3 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                        rows="3"
                                    />
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-bold text-black-700">
                                        Teléfono de Contacto *
                                    </label>
                                    <div className="relative">
                                        <Phone className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-black-600" />
                                        <input
                                            type="tel"
                                            value={delivery.phone}
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
                                    <p className="mt-1.5 flex items-center gap-1 text-xs text-black-500">
                                        <Sparkles className="h-3 w-3" />
                                        Te contactaremos para coordinar la
                                        entrega
                                    </p>
                                </div>
                            </div>
                        )}

                        <div>
                            <label className="mb-2 block text-sm font-bold text-black-700">
                                Notas Adicionales (opcional)
                            </label>
                            <textarea
                                value={delivery.notes}
                                onChange={(e) =>
                                    handleChange('notes', e.target.value)
                                }
                                placeholder="Horario preferido, instrucciones especiales, punto de referencia..."
                                className="w-full resize-none rounded-xl border-2 border-black-200 p-3 text-sm font-medium transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                rows="2"
                            />
                        </div>

                        {/* 🎯 Información de envío con promoción */}
                        <motion.div
                            initial={{ scale: 0.95, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            transition={{ delay: 0.2 }}
                            className={`rounded-xl border-2 p-4 ${
                                hasFreeDelivery
                                    ? 'border-green-300 bg-linear-to-br from-green-50 to-emerald-50'
                                    : 'border-black-200 bg-linear-to-br from-black-50 to-slate-50'
                            }`}
                        >
                            <div className="mb-2 flex items-center gap-2">
                                <Truck
                                    className={`h-4 w-4 ${hasFreeDelivery ? 'text-green-600' : 'text-black-600'}`}
                                />
                                <h4
                                    className={`text-sm font-bold ${hasFreeDelivery ? 'text-green-900' : 'text-black-900'}`}
                                >
                                    Información de Envío
                                </h4>
                            </div>
                            <ul className="space-y-1.5">
                                {hasFreeDelivery ? (
                                    <>
                                        <li className="flex items-center gap-2 text-xs text-green-700">
                                            <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                            <span className="flex items-center gap-2">
                                                Costo base:
                                                <span className="text-green-600/60 line-through">
                                                    $
                                                    {originalDeliveryCost.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                                <span className="font-bold text-green-700">
                                                    ¡GRATIS!
                                                </span>
                                            </span>
                                        </li>
                                        {automaticDeliveryPromotion && (
                                            <li className="flex items-center gap-2 text-xs text-green-700">
                                                <Gift className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                                <span className="font-bold">
                                                    {
                                                        automaticDeliveryPromotion.description
                                                    }
                                                </span>
                                            </li>
                                        )}
                                        {isFreeByMinimum &&
                                            !automaticDeliveryPromotion && (
                                                <li className="flex items-center gap-2 text-xs text-green-700">
                                                    <Gift className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                                    <span className="font-bold">
                                                        Envío gratis por compra
                                                        mayor a $
                                                        {config.delivery.free_delivery_minimum.toFixed(
                                                            2,
                                                        )}
                                                    </span>
                                                </li>
                                            )}
                                    </>
                                ) : deliveryDiscount > 0 ? (
                                    <>
                                        <li className="flex items-center gap-2 text-xs text-black-700">
                                            <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                            <span className="flex items-center gap-2">
                                                Costo base:
                                                <span className="text-black-500 line-through">
                                                    $
                                                    {originalDeliveryCost.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                                <span className="font-bold text-green-700">
                                                    $
                                                    {finalDeliveryCost.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </span>
                                        </li>
                                        <li className="flex items-center gap-2 text-xs text-green-700">
                                            <Gift className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                            <span>
                                                Descuento:{' '}
                                                <span className="font-bold">
                                                    -$
                                                    {deliveryDiscount.toFixed(
                                                        2,
                                                    )}
                                                </span>
                                            </span>
                                        </li>
                                    </>
                                ) : (
                                    <li className="flex items-center gap-2 text-xs text-black-700">
                                        <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                        <span>
                                            Costo base:{' '}
                                            <span className="font-bold">
                                                $
                                                {originalDeliveryCost.toFixed(
                                                    2,
                                                )}
                                            </span>
                                        </span>
                                    </li>
                                )}
                                <li className="flex items-center gap-2 text-xs text-black-700">
                                    <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                    <span>
                                        Envío gratis en pedidos mayores a{' '}
                                        <span className="font-bold">
                                            $
                                            {config.delivery.free_delivery_minimum.toFixed(
                                                2,
                                            )}
                                        </span>
                                    </span>
                                </li>
                                <li className="flex items-center gap-2 text-xs text-black-700">
                                    <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-green-600" />
                                    <span>
                                        Tiempo estimado:{' '}
                                        <span className="font-bold">
                                            1-2 días hábiles
                                        </span>
                                    </span>
                                </li>
                            </ul>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <div className="mt-6 flex justify-between border-t-2 border-black-100 pt-6">
                <motion.button
                    whileHover={{ scale: 1.05, x: -5 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={onBack}
                    className="flex items-center gap-2 rounded-xl border-2 border-black-200 px-6 py-3 text-sm font-bold text-black-700 transition-all hover:border-black-300 hover:bg-black-100"
                >
                    <ChevronLeft className="h-4 w-4" />
                    Atrás
                </motion.button>
                <motion.button
                    whileHover={{
                        scale: canContinue ? 1.05 : 1,
                        x: canContinue ? 5 : 0,
                    }}
                    whileTap={{ scale: canContinue ? 0.95 : 1 }}
                    disabled={!canContinue}
                    onClick={() => canContinue && handleContinue()}
                    className={`flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold transition-all ${
                        canContinue
                            ? 'bg-linear-to-r from-mbe-accent to-mbe-accent-2 text-white shadow-xl shadow-black-500/30 hover:from-mbe-accent-2 hover:to-mbe-accent'
                            : 'cursor-not-allowed bg-black-200 text-black-400'
                    }`}
                >
                    Continuar
                    {canContinue && (
                        <motion.div
                            animate={{ x: [0, 5, 0] }}
                            transition={{ duration: 1, repeat: Infinity }}
                        >
                            <ChevronRight className="h-5 w-5" />
                        </motion.div>
                    )}
                </motion.button>
            </div>
        </div>
    );
};

export default Step3Delivery;
