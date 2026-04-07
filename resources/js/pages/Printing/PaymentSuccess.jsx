import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle,
    CreditCard,
    Printer,
} from 'lucide-react';

export default function PaymentSuccess({ printOrder, payment }) {
    const { auth } = usePage().props;

    const isSuccess = payment && payment.status === 'completed';
    const isFailed =
        payment &&
        (payment.status === 'failed' || payment.status === 'cancelled');
    const isPending =
        payment &&
        (payment.status === 'pending' || payment.status === 'processing');
    const noPayment = !payment;

    return (
        <AppLayout>
            <Head title="Resultado del Pago" />

            <div className="mx-auto max-w-4xl px-4 py-8">
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="space-y-6"
                >
                    {/* Header */}
                    <div className="text-center">
                        {isSuccess && (
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ type: 'spring', duration: 0.5 }}
                                className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100"
                            >
                                <CheckCircle className="h-12 w-12 text-green-600" />
                            </motion.div>
                        )}

                        {isFailed && (
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ type: 'spring', duration: 0.5 }}
                                className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-red-100"
                            >
                                <AlertCircle className="h-12 w-12 text-red-600" />
                            </motion.div>
                        )}

                        {isPending && (
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ type: 'spring', duration: 0.5 }}
                                className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-yellow-100"
                            >
                                <CreditCard className="h-12 w-12 text-yellow-600" />
                            </motion.div>
                        )}

                        {noPayment && (
                            <motion.div
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ type: 'spring', duration: 0.5 }}
                                className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100"
                            >
                                <AlertCircle className="h-12 w-12 text-gray-600" />
                            </motion.div>
                        )}

                        <h1
                            className={`mb-2 text-3xl font-bold ${
                                isSuccess
                                    ? 'text-green-600'
                                    : isFailed
                                      ? 'text-red-600'
                                      : isPending
                                        ? 'text-yellow-600'
                                        : 'text-gray-600'
                            }`}
                        >
                            {isSuccess && '¡Pago Completado Exitosamente!'}
                            {isFailed && 'Pago Rechazado'}
                            {isPending &&
                                (payment?.gateway === 'transfer'
                                    ? 'Comprobante Enviado'
                                    : payment?.gateway === 'cash'
                                      ? 'Pago Contra Entrega'
                                      : 'Pago en Proceso')}
                            {noPayment && 'No se encontró información de pago'}
                        </h1>

                        <p className="text-gray-600">
                            {isSuccess &&
                                'Tu pago ha sido procesado correctamente. Tu orden de impresión está lista para ser procesada.'}
                            {isFailed &&
                                (payment?.reason_message ||
                                    'El pago no pudo ser procesado. Por favor intenta nuevamente.')}
                            {isPending &&
                                (payment?.gateway === 'transfer'
                                    ? 'Hemos recibido tu comprobante de transferencia. Un administrador verificará el pago y te notificaremos cuando se confirme.'
                                    : payment?.gateway === 'cash'
                                      ? 'Has seleccionado pago contra entrega. El pago se realizará cuando recojas o recibas tu pedido.'
                                      : 'Tu pago está siendo procesado. Te notificaremos cuando se complete.')}
                            {noPayment &&
                                'No se encontró información de pago para esta orden.'}
                        </p>
                    </div>

                    {/* Información del Pago */}
                    {payment ? (
                        <div className="rounded-xl border-2 border-gray-200 bg-white p-6 shadow-lg">
                            <h2 className="mb-4 flex items-center gap-2 text-xl font-bold text-gray-900">
                                <CreditCard className="h-5 w-5" />
                                Detalles del Pago
                            </h2>
                            <div className="space-y-3">
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Número de Referencia:
                                    </span>
                                    <span className="font-mono font-bold text-gray-900">
                                        {payment.reference_number}
                                    </span>
                                </div>
                                {payment.transaction_id && (
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">
                                            ID de Transacción:
                                        </span>
                                        <span className="font-mono text-gray-900">
                                            {payment.transaction_id}
                                        </span>
                                    </div>
                                )}
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Monto:
                                    </span>
                                    <span className="font-bold text-gray-900">
                                        ${parseFloat(payment.amount).toFixed(2)}{' '}
                                        {payment.currency}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Estado:
                                    </span>
                                    <span
                                        className={`font-bold ${
                                            isSuccess
                                                ? 'text-green-600'
                                                : isFailed
                                                  ? 'text-red-600'
                                                  : 'text-yellow-600'
                                        }`}
                                    >
                                        {payment.status_label}
                                    </span>
                                </div>
                                {payment.gateway && (
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">
                                            Método:
                                        </span>
                                        <span className="text-gray-900">
                                            {payment.gateway === 'cybersource'
                                                ? 'Tarjeta'
                                                : payment.gateway === 'transfer'
                                                  ? 'Transferencia'
                                                  : payment.gateway === 'cash'
                                                    ? 'Contra entrega'
                                                    : payment.gateway}
                                        </span>
                                    </div>
                                )}
                                {payment.completed_at && (
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">
                                            Fecha de Pago:
                                        </span>
                                        <span className="text-gray-900">
                                            {new Date(
                                                payment.completed_at,
                                            ).toLocaleString('es-SV')}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="rounded-xl border-2 border-gray-200 bg-white p-6 shadow-lg">
                            <p className="text-center text-gray-600">
                                No hay información de pago disponible. Si acabas
                                de realizar un pago, por favor espera unos
                                momentos o contacta a soporte.
                            </p>
                        </div>
                    )}

                    {/* Información de la Orden */}
                    {printOrder && (
                        <div className="rounded-xl border-2 border-gray-200 bg-white p-6 shadow-lg">
                            <h2 className="mb-4 flex items-center gap-2 text-xl font-bold text-gray-900">
                                <Printer className="h-5 w-5" />
                                Información de la Orden
                            </h2>
                            <div className="space-y-3">
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Número de Orden:
                                    </span>
                                    <span className="font-mono font-bold text-gray-900">
                                        #{printOrder.order_number}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Total:
                                    </span>
                                    <span className="font-bold text-gray-900">
                                        $
                                        {parseFloat(
                                            printOrder.total || 0,
                                        ).toFixed(2)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">
                                        Estado:
                                    </span>
                                    <span className="font-bold text-gray-900">
                                        {printOrder.status_label ||
                                            printOrder.status}
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Acciones */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:justify-center">
                        {auth?.user ? (
                            <>
                                {printOrder && (
                                    <Link
                                        href={route(
                                            'print-orders.show',
                                            printOrder.order_number,
                                        )}
                                        className="flex items-center justify-center gap-2 rounded-xl bg-gray-600 px-6 py-3 font-bold text-white transition-all hover:bg-gray-700"
                                    >
                                        <ArrowLeft className="h-5 w-5" />
                                        Ver Detalles de la Orden
                                    </Link>
                                )}
                                <Link
                                    href={route('print-orders.my-orders')}
                                    className="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-300 bg-white px-6 py-3 font-bold text-gray-700 transition-all hover:bg-gray-50"
                                >
                                    Ver Todas las Órdenes
                                </Link>
                            </>
                        ) : (
                            <Link
                                href={route('login')}
                                className="flex items-center justify-center gap-2 rounded-xl bg-[#E31C25] px-6 py-3 font-bold text-white transition-all hover:bg-[#c91821]"
                            >
                                Iniciar sesión para ver tus órdenes
                            </Link>
                        )}
                    </div>

                    {/* Mensaje adicional para éxito */}
                    {isSuccess && (
                        <div className="rounded-xl border-2 border-green-200 bg-green-50 p-6">
                            <p className="text-center text-sm text-green-800">
                                Recibirás una confirmación por correo
                                electrónico con los detalles de tu pago. Tu
                                orden será procesada según el método de entrega
                                seleccionado.
                            </p>
                        </div>
                    )}

                    {/* Mensaje adicional para error */}
                    {isFailed && (
                        <div className="rounded-xl border-2 border-red-200 bg-red-50 p-6">
                            <p className="text-center text-sm text-red-800">
                                Si el problema persiste, por favor contacta a
                                nuestro equipo de soporte. Puedes intentar el
                                pago nuevamente desde los detalles de tu orden.
                            </p>
                        </div>
                    )}
                </motion.div>
            </div>
        </AppLayout>
    );
}
