import { usePage } from '@inertiajs/react';
import axios from 'axios';
import { motion } from 'framer-motion';
import {
    Banknote,
    ChevronLeft,
    CreditCard,
    DollarSign,
    ExternalLink,
    Loader2,
    Package,
    Shield,
    Upload,
} from 'lucide-react';
import { useState } from 'react';
import Swal from 'sweetalert2';

const Step5Payment = ({
    data,
    onBack,
    onSubmit,
    orderNumber,
    total,
    isSubmitting,
}) => {
    const { props } = usePage();
    const csrfTokenFromProps = props?.csrf_token;
    const [isInitiatingPayment, setIsInitiatingPayment] = useState(false);
    const [printOrderId, setPrintOrderId] = useState(null);
    const [paymentMethod, setPaymentMethod] = useState('cybersource');
    const [transferProof, setTransferProof] = useState(null);
    const [transferReference, setTransferReference] = useState('');
    const [transferNotes, setTransferNotes] = useState('');

    const finalTotal =
        total || data.finalTotal || data.priceBreakdown?.total || 0;

    const handleInitiatePayment = async () => {
        setIsInitiatingPayment(true);

        try {
            // Primero crear la orden si no existe (cuando viene del wizard)
            let orderId = printOrderId;

            if (!orderId && !orderNumber) {
                // Crear la orden primero
                const formData = new FormData();

                // Agregar archivos (file puede ser el objeto {file, name, ...} o el File directo)
                if (data.files && data.files.length > 0) {
                    data.files.forEach((fileObj, index) => {
                        const file = fileObj?.file ?? fileObj;
                        if (file instanceof File) {
                            formData.append(`files[${index}]`, file);
                        }
                    });
                }

                // Configuración (asegurar binding y doubleSided como '1'/'0' para evitar errores de validación)
                Object.keys(data.config || {}).forEach((key) => {
                    const value = data.config[key];
                    if (value === null || value === undefined) return;
                    if (key === 'binding' || key === 'doubleSided') {
                        formData.append(`config[${key}]`, value ? '1' : '0');
                    } else if (value !== '') {
                        formData.append(`config[${key}]`, value);
                    }
                });

                // Delivery
                Object.keys(data.delivery || {}).forEach((key) => {
                    const value = data.delivery[key];
                    if (value !== null && value !== undefined && value !== '') {
                        formData.append(`delivery[${key}]`, value);
                    }
                });

                // Customer
                Object.keys(data.customer || {}).forEach((key) => {
                    const value = data.customer[key];
                    if (value !== null && value !== undefined && value !== '') {
                        formData.append(`customer[${key}]`, value);
                    }
                });

                // Promociones
                if (data.deliveryPromotion) {
                    formData.append(
                        'delivery_promotion_id',
                        data.deliveryPromotion.id,
                    );
                    if (data.deliveryPromotion.coupon_code) {
                        formData.append(
                            'delivery_coupon_code',
                            data.deliveryPromotion.coupon_code,
                        );
                    }
                }

                if (data.generalPromotion) {
                    formData.append(
                        'general_promotion_id',
                        data.generalPromotion.id,
                    );
                    if (data.generalPromotion.coupon_code) {
                        formData.append(
                            'general_coupon_code',
                            data.generalPromotion.coupon_code,
                        );
                    }
                }

                formData.append('final_delivery_cost', data.deliveryCost || 0);
                formData.append('final_total', finalTotal);

                const createCsrf =
                    csrfTokenFromProps ??
                    document.querySelector('meta[name="csrf-token"]')?.content ??
                    '';
                if (!createCsrf?.trim()) {
                    throw new Error(
                        'El token CSRF está vacío. Por favor, recarga la página.',
                    );
                }
                // Crear la orden con Accept: application/json para recibir JSON
                const createResponse = await axios.post(
                    '/print-orders',
                    formData,
                    {
                        headers: {
                            'X-CSRF-TOKEN': createCsrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data',
                            Accept: 'application/json',
                        },
                        withCredentials: true,
                    },
                );

                // Si la respuesta tiene order_id, usarlo
                if (createResponse.data?.order_id) {
                    orderId = createResponse.data.order_id;
                    setPrintOrderId(orderId);
                } else if (createResponse.data?.order_number) {
                    // Si solo tenemos order_number, buscar la orden
                    const orderResponse = await axios.get(
                        `/print-orders/track?order_number=${createResponse.data.order_number}`,
                    );
                    if (orderResponse.data?.orderData?.id) {
                        orderId = orderResponse.data.orderData.id;
                        setPrintOrderId(orderId);
                    }
                }
            }

            // Si tenemos orderNumber pero no orderId, buscar la orden
            if (!orderId && orderNumber) {
                const orderResponse = await axios.get(
                    `/print-orders/track?order_number=${orderNumber}`,
                );
                if (orderResponse.data?.orderData?.id) {
                    orderId = orderResponse.data.orderData.id;
                    setPrintOrderId(orderId);
                }
            }

            // Si aún no tenemos orderId, mostrar error
            if (!orderId) {
                throw new Error(
                    'No se pudo obtener el ID de la orden. Por favor, recarga la página e intenta nuevamente.',
                );
            }

            if (paymentMethod === 'transfer' && !transferProof) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Comprobante requerido',
                    text: 'Debes subir el comprobante de tu transferencia.',
                });
                setIsInitiatingPayment(false);
                return;
            }

            const csrfToken =
                csrfTokenFromProps ??
                document.querySelector('meta[name="csrf-token"]')?.content ??
                '';
            if (!csrfToken || csrfToken.trim() === '') {
                throw new Error(
                    'El token CSRF está vacío. Por favor, recarga la página.',
                );
            }

            let response;
            if (paymentMethod === 'transfer' && transferProof) {
                const formData = new FormData();
                formData.append('gateway', paymentMethod);
                formData.append('total', finalTotal);
                formData.append('transfer_proof', transferProof);
                if (transferReference) formData.append('transfer_reference', transferReference);
                if (transferNotes) formData.append('transfer_notes', transferNotes);

                response = await axios.post(
                    `/print-orders/${orderId}/payment`,
                    formData,
                    {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                            'Content-Type': 'multipart/form-data',
                        },
                        withCredentials: true,
                    },
                );
            } else {
                response = await axios.post(
                    `/print-orders/${orderId}/payment`,
                    { total: finalTotal, gateway: paymentMethod },
                    {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        withCredentials: true,
                    },
                );
            }

            if (response.data.success && response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
            } else if (response.data.error) {
                throw new Error(response.data.error);
            } else {
                throw new Error('No se recibió URL de redirección');
            }
        } catch (error) {
            console.error('Error iniciando pago:', error);

            if (error.response?.status === 419) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de sesión',
                    text: 'Tu sesión ha expirado. Por favor, recarga la página e intenta nuevamente.',
                    confirmButtonText: 'Recargar página',
                }).then(() => {
                    window.location.reload();
                });
                setIsInitiatingPayment(false);
                return;
            }

            const errorMessage =
                error.response?.data?.error ||
                error.response?.data?.message ||
                error.message ||
                'Error al iniciar el pago. Por favor intenta de nuevo.';

            Swal.fire({
                icon: 'error',
                title: 'Error al iniciar el pago',
                text: errorMessage,
                confirmButtonText: 'Entendido',
            });

            setIsInitiatingPayment(false);
        }
    };

    return (
        <div>
            <div className="mb-6">
                <div className="mb-2 flex items-center gap-2">
                    <motion.div
                        animate={{
                            scale: [1, 1.1, 1],
                        }}
                        transition={{
                            duration: 2,
                            repeat: Infinity,
                            repeatDelay: 3,
                        }}
                        className="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-black-700 to-black-900 shadow-lg shadow-black-500/30"
                    >
                        <CreditCard className="h-5 w-5 text-white" />
                    </motion.div>
                    <div>
                        <h2 className="text-2xl font-bold text-black-900">
                            Procesar Pago
                        </h2>
                        <p className="text-sm text-black-600">
                            Total:{' '}
                            <span className="font-bold text-black-600">
                                ${finalTotal.toFixed(2)}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="grid grid-cols-1 gap-6 lg:grid-cols-2"
            >
                <div className="space-y-5">
                    <div>
                        <h3 className="mb-3 flex items-center gap-2 text-base font-bold text-black-900">
                            <Package className="h-4 w-4 text-black-600" />
                            Resumen del Pedido
                        </h3>

                        <div className="rounded-xl border-2 border-black-200 bg-linear-to-br from-black-50 to-black-100 p-5">
                            <h4 className="mb-3 text-sm font-bold text-black-900">
                                Detalles del Pedido
                            </h4>
                            <div className="space-y-2 text-sm">
                                {data.customer?.name && (
                                    <div className="flex justify-between">
                                        <span className="text-black-600">
                                            Cliente:
                                        </span>
                                        <span className="font-bold text-black-900">
                                            {data.customer.name}
                                        </span>
                                    </div>
                                )}
                                {data.customer?.email && (
                                    <div className="flex justify-between">
                                        <span className="text-black-600">
                                            Email:
                                        </span>
                                        <span className="font-bold text-black-900">
                                            {data.customer.email}
                                        </span>
                                    </div>
                                )}
                                {orderNumber && (
                                    <div className="flex justify-between">
                                        <span className="text-black-600">
                                            Pedido:
                                        </span>
                                        <span className="font-bold text-black-900">
                                            #{orderNumber}
                                        </span>
                                    </div>
                                )}
                                <div className="border-t-2 border-black-300 pt-3">
                                    <div className="flex justify-between">
                                        <span className="font-bold text-black-900">
                                            Total a Pagar:
                                        </span>
                                        <span className="text-lg font-bold text-black-600">
                                            ${finalTotal.toFixed(2)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Selector de método de pago */}
                    <div className="rounded-xl border-2 border-black-200 bg-white p-5">
                        <h3 className="mb-4 text-base font-bold text-black-900">
                            Método de pago
                        </h3>
                        <div className="space-y-3">
                            <label
                                className={`flex cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-colors ${
                                    paymentMethod === 'cybersource'
                                        ? 'border-mbe-accent bg-red-50'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cybersource"
                                    checked={paymentMethod === 'cybersource'}
                                    onChange={() => setPaymentMethod('cybersource')}
                                    className="h-4 w-4"
                                />
                                <CreditCard className="h-5 w-5 text-black-600" />
                                <div>
                                    <span className="font-semibold">Tarjeta</span>
                                    <p className="text-xs text-gray-500">Pago seguro con CyberSource</p>
                                </div>
                            </label>
                            <label
                                className={`flex cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-colors ${
                                    paymentMethod === 'transfer'
                                        ? 'border-mbe-accent bg-red-50'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="transfer"
                                    checked={paymentMethod === 'transfer'}
                                    onChange={() => setPaymentMethod('transfer')}
                                    className="h-4 w-4"
                                />
                                <Upload className="h-5 w-5 text-black-600" />
                                <div>
                                    <span className="font-semibold">Transferencia</span>
                                    <p className="text-xs text-gray-500">Sube tu comprobante</p>
                                </div>
                            </label>
                            <label
                                className={`flex cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-colors ${
                                    paymentMethod === 'cash'
                                        ? 'border-mbe-accent bg-red-50'
                                        : 'border-gray-200 hover:bg-gray-50'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cash"
                                    checked={paymentMethod === 'cash'}
                                    onChange={() => setPaymentMethod('cash')}
                                    className="h-4 w-4"
                                />
                                <Banknote className="h-5 w-5 text-black-600" />
                                <div>
                                    <span className="font-semibold">Contra entrega</span>
                                    <p className="text-xs text-gray-500">Paga al recoger</p>
                                </div>
                            </label>
                        </div>

                        {paymentMethod === 'transfer' && (
                            <div className="mt-4 space-y-3 border-t border-gray-200 pt-4">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-black-700">
                                        Comprobante *
                                    </label>
                                    <input
                                        type="file"
                                        accept="image/*,.pdf"
                                        onChange={(e) => setTransferProof(e.target.files?.[0] || null)}
                                        className="block w-full rounded-lg border border-gray-300 text-sm file:mr-4 file:rounded-l-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2"
                                    />
                                    {transferProof && (
                                        <p className="mt-1 text-xs text-green-600">{transferProof.name}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-black-700">
                                        Referencia (opcional)
                                    </label>
                                    <input
                                        type="text"
                                        value={transferReference}
                                        onChange={(e) => setTransferReference(e.target.value)}
                                        placeholder="REF-12345"
                                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-black-700">
                                        Notas (opcional)
                                    </label>
                                    <textarea
                                        value={transferNotes}
                                        onChange={(e) => setTransferNotes(e.target.value)}
                                        rows={2}
                                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                    />
                                </div>
                            </div>
                        )}
                    </div>

                    {paymentMethod === 'cybersource' && (
                    <div className="rounded-xl border-2 border-blue-200 bg-linear-to-br from-blue-50 to-indigo-50 p-5">
                        <div className="flex items-start gap-3">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500">
                                <ExternalLink className="h-5 w-5 text-white" />
                            </div>
                            <div className="flex-1">
                                <h3 className="mb-2 text-base font-bold text-blue-900">
                                    Serás redirigido a CyberSource
                                </h3>
                                <p className="mb-3 text-sm text-blue-800">
                                    Al hacer clic en "Pagar ahora", serás
                                    redirigido de forma segura a CyberSource
                                    para completar tu pago. Tus datos de tarjeta
                                    se procesarán de forma segura y no se
                                    almacenarán en nuestros servidores.
                                </p>
                                <div className="flex items-center gap-2 text-xs text-blue-700">
                                    <Shield className="h-4 w-4" />
                                    <span>
                                        Pago 100% seguro con encriptación SSL
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    )}

                    <div className="rounded-xl border-2 border-green-200 bg-linear-to-br from-green-50 to-emerald-50 p-4">
                        <div className="flex items-start gap-3">
                            <Shield className="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
                            <div>
                                <h4 className="mb-1 text-sm font-bold text-green-900">
                                    Pago Seguro
                                </h4>
                                <p className="text-xs text-green-700">
                                    Tu información está protegida con
                                    encriptación de nivel bancario. No
                                    almacenamos tus datos de tarjeta.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="space-y-5">
                    <h3 className="text-base font-bold text-black-900">
                        Vista Previa
                    </h3>

                    <div className="rounded-xl border-2 border-black-200 bg-linear-to-br from-black-50 to-black-100 p-5">
                        <h4 className="mb-3 text-sm font-bold text-black-900">
                            Resumen de Pago
                        </h4>
                        <div className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-black-600">
                                    Subtotal:
                                </span>
                                <span className="font-bold text-black-900">
                                    $
                                    {(
                                        data.priceBreakdown?.base_subtotal || 0
                                    ).toFixed(2)}
                                </span>
                            </div>
                            {data.deliveryCost > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-black-600">
                                        Envío:
                                    </span>
                                    <span className="font-bold text-black-900">
                                        ${(data.deliveryCost || 0).toFixed(2)}
                                    </span>
                                </div>
                            )}
                            {data.deliveryPromotion && (
                                <div className="flex justify-between text-green-600">
                                    <span>Descuento de envío:</span>
                                    <span className="font-bold">
                                        -$
                                        {(
                                            data.deliveryPromotion
                                                .discount_value || 0
                                        ).toFixed(2)}
                                    </span>
                                </div>
                            )}
                            <div className="border-t-2 border-black-300 pt-3">
                                <div className="flex justify-between">
                                    <span className="font-bold text-black-900">
                                        Total a Pagar:
                                    </span>
                                    <span className="text-lg font-bold text-black-600">
                                        ${finalTotal.toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </motion.div>

            <div className="mt-6 flex justify-between border-t-2 border-black-100 pt-6">
                <motion.button
                    whileHover={{ scale: 1.05, x: -5 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={onBack}
                    disabled={isInitiatingPayment || isSubmitting}
                    className="flex items-center gap-2 rounded-xl border-2 border-black-200 px-6 py-3 text-sm font-bold text-black-700 transition-all hover:border-black-300 hover:bg-black-100 disabled:opacity-50"
                >
                    <ChevronLeft className="h-4 w-4" />
                    Atrás
                </motion.button>

                <motion.button
                    whileHover={{ scale: !isInitiatingPayment ? 1.05 : 1 }}
                    whileTap={{ scale: !isInitiatingPayment ? 0.95 : 1 }}
                    disabled={isInitiatingPayment || isSubmitting}
                    onClick={handleInitiatePayment}
                    className={`flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold transition-all ${
                        !isInitiatingPayment && !isSubmitting
                            ? 'bg-linear-to-r from-mbe-accent to-mbe-accent-2 text-white shadow-xl shadow-black-500/30 hover:from-mbe-accent-2 hover:to-mbe-accent hover:shadow-2xl hover:shadow-black-500/40'
                            : 'cursor-not-allowed bg-linear-to-r from-black-400 to-black-500 text-white'
                    }`}
                >
                    {isInitiatingPayment || isSubmitting ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            {isInitiatingPayment
                                ? 'Redirigiendo...'
                                : 'Procesando...'}
                        </>
                    ) : (
                        <>
                            <DollarSign className="h-4 w-4" />
                            {paymentMethod === 'cybersource'
                                ? `Pagar $${finalTotal.toFixed(2)}`
                                : paymentMethod === 'transfer'
                                  ? 'Enviar comprobante'
                                  : 'Confirmar pago contra entrega'}
                            {paymentMethod === 'cybersource' && (
                                <ExternalLink className="h-4 w-4" />
                            )}
                        </>
                    )}
                </motion.button>
            </div>
        </div>
    );
};

export default Step5Payment;
