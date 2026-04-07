// resources/js/Pages/PrintOrders/Success.jsx

import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Mail,
    Package,
    Plus,
} from 'lucide-react';

const Success = ({ order }) => {
    const { auth } = usePage().props;
    const isAuthenticated = auth?.user !== null && auth?.user !== undefined;
    return (
        <>
            <Head title="Pedido Creado Exitosamente" />

            <div className="flex min-h-screen items-center justify-center bg-linear-to-br from-green-50 via-white to-blue-50 p-4 md:p-8">
                <motion.div
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="w-full max-w-2xl"
                >
                    {/* Success Icon */}
                    <motion.div
                        initial={{ scale: 0 }}
                        animate={{ scale: 1 }}
                        transition={{ delay: 0.2, type: 'spring' }}
                        className="mb-6 flex justify-center"
                    >
                        <div className="flex h-24 w-24 items-center justify-center rounded-full bg-green-100">
                            <CheckCircle2 className="h-16 w-16 text-green-600" />
                        </div>
                    </motion.div>

                    {/* Main Card */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3 }}
                        className="rounded-2xl bg-white p-8 text-center shadow-xl"
                    >
                        <h1 className="mb-3 text-3xl font-bold text-gray-900">
                            ¡Pedido Creado Exitosamente! 🎉
                        </h1>
                        <p className="mb-6 text-gray-600">
                            Tu pedido ha sido recibido y está siendo procesado
                        </p>

                        {/* Order Number */}
                        <div className="mb-6 rounded-xl bg-black-50 p-6">
                            <p className="mb-2 text-sm text-gray-600">
                                Número de Pedido
                            </p>
                            <p className="font-mono text-3xl font-bold text-black-900">
                                {order.order_number}
                            </p>
                            <p className="mt-2 text-sm text-gray-500">
                                Guarda este número para rastrear tu pedido
                            </p>
                        </div>

                        {/* Total */}
                        <div className="mb-6 flex items-center justify-center gap-3 text-2xl">
                            <span className="text-gray-600">Total:</span>
                            <span className="font-bold text-gray-900">
                                ${order.total}
                            </span>
                        </div>

                        {/* Delivery Method */}
                        <div className="mb-8 flex items-center justify-center gap-2 text-gray-600">
                            <Package className="h-5 w-5" />
                            <span>
                                {order.delivery_method === 'pickup'
                                    ? 'Recoger en tienda'
                                    : 'Envío a domicilio'}
                            </span>
                        </div>

                        {/* Info Box */}
                        <div className="mb-6 rounded-xl border-2 border-yellow-200 bg-yellow-50 p-4 text-left">
                            <div className="flex items-start gap-3">
                                <Mail className="mt-0.5 h-5 w-5 shrink-0 text-yellow-600" />
                                <div className="text-sm">
                                    <p className="mb-1 font-semibold text-gray-900">
                                        Revisa tu correo electrónico
                                    </p>
                                    <p className="text-gray-600">
                                        Te hemos enviado un correo con todos los
                                        detalles de tu pedido y las
                                        instrucciones para completar el pago.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                            {isAuthenticated ? (
                                <Link
                                    href={route('print-orders.my-orders')}
                                    className="flex items-center justify-center gap-2 rounded-lg border-2 border-black-300 bg-white px-6 py-3 font-medium text-black-700 transition-all hover:border-black-400 hover:bg-gray-50"
                                >
                                    <ArrowLeft className="h-5 w-5" />
                                    Mis Pedidos
                                </Link>
                            ) : (
                                <a
                                    href="https://mbeelsalvador.com/"
                                    className="flex items-center justify-center gap-2 rounded-lg border-2 border-black-300 bg-white px-6 py-3 font-medium text-black-700 transition-all hover:border-black-400 hover:bg-gray-50"
                                >
                                    <ArrowLeft className="h-5 w-5" />
                                    Regresar
                                </a>
                            )}
                            <Link
                                href={route('print-orders.track')}
                                className="flex items-center justify-center gap-2 rounded-lg border-2 border-black-300 bg-white px-6 py-3 font-medium text-black-700 transition-all hover:border-black-400 hover:bg-gray-50"
                            >
                                Rastrear Pedido
                                <ArrowRight className="h-5 w-5" />
                            </Link>
                            <Link
                                href={route('print-orders.create')}
                                className="flex items-center justify-center gap-2 rounded-lg bg-black-900 px-6 py-3 font-medium text-white transition-all hover:bg-black-700 hover:shadow-lg"
                            >
                                <Plus className="h-5 w-5" />
                                Nuevo Pedido
                            </Link>
                        </div>
                    </motion.div>

                    {/* What's Next */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.5 }}
                        className="mt-6 rounded-xl bg-white p-6 shadow-md"
                    >
                        <h2 className="mb-4 font-bold text-gray-900">
                            ¿Qué sigue?
                        </h2>
                        <div className="space-y-3 text-sm text-gray-600">
                            <div className="flex items-start gap-3">
                                <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-black-100">
                                    <span className="text-xs font-bold text-black-600">
                                        1
                                    </span>
                                </div>
                                <p>
                                    Recibirás un correo de confirmación con los
                                    detalles del pedido
                                </p>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-black-100">
                                    <span className="text-xs font-bold text-black-600">
                                        2
                                    </span>
                                </div>
                                <p>
                                    Realiza el pago según las instrucciones
                                    enviadas
                                </p>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-black-100">
                                    <span className="text-xs font-bold text-black-600">
                                        3
                                    </span>
                                </div>
                                <p>
                                    Una vez confirmado el pago, comenzaremos a
                                    imprimir tu pedido
                                </p>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-black-100">
                                    <span className="text-xs font-bold text-black-600">
                                        4
                                    </span>
                                </div>
                                <p>
                                    Te notificaremos cuando tu pedido esté listo
                                    para recoger o envío
                                </p>
                            </div>
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </>
    );
};

export default Success;
