import { Head, Link } from '@inertiajs/react';
import axios from 'axios';
import { AnimatePresence, motion } from 'framer-motion';
import {
    AlertCircle,
    ArrowLeft,
    Bell,
    CheckCircle2,
    Clock,
    FileText,
    Lightbulb,
    Mail,
    MessageCircle,
    Package,
    Phone,
    Printer,
    Search,
    Truck,
} from 'lucide-react';
import React, { useState } from 'react';

const OrderTracking = ({
    orderData = null,
    showSearch = true,
    backUrl = null,
}) => {
    const [orderNumber, setOrderNumber] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [order, setOrder] = useState(orderData?.order || null);
    const [notFound, setNotFound] = useState(false);

    const statusConfig = {
        pending: { label: 'Pendiente', icon: Clock, color: 'black' },
        payment_pending: {
            label: 'Esperando Pago',
            icon: AlertCircle,
            color: 'yellow',
        },
        in_queue: { label: 'En Cola', icon: Package, color: 'blue' },
        printing: { label: 'Imprimiendo', icon: Printer, color: 'purple' },
        ready: { label: 'Listo', icon: CheckCircle2, color: 'green' },
        shipped: { label: 'En Camino', icon: Truck, color: 'blue' },
        delivered: { label: 'Entregado', icon: CheckCircle2, color: 'green' },
        cancelled: { label: 'Cancelado', icon: AlertCircle, color: 'black' },
    };

    const handleSearch = async () => {
        if (!orderNumber.trim()) return;

        setIsSearching(true);
        setNotFound(false);
        setOrder(null);

        try {
            const response = await axios.get(
                route('print-orders.show', orderNumber.toUpperCase()),
            );

            if (response.data.success) {
                setOrder(response.data.orderData.order);
                setNotFound(false);
            } else {
                setOrder(null);
                setNotFound(true);
            }
        } catch (error) {
            console.error('Error buscando pedido:', error);
            setOrder(null);
            setNotFound(true);
        } finally {
            setIsSearching(false);
        }
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleString('es-SV', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const getStatusMessage = (status) => {
        const messages = {
            pending: 'Tu pedido ha sido recibido y está siendo procesado.',
            payment_pending: 'Estamos esperando la confirmación de tu pago.',
            in_queue:
                'Tu pedido está en cola de impresión. Pronto comenzaremos a imprimirlo.',
            printing: 'Tu pedido está siendo impreso en este momento.',
            ready: 'Tu pedido está listo para ser recogido en la ubicación seleccionada.',
            shipped: 'Tu pedido está en camino a la dirección de entrega.',
            delivered: 'Tu pedido ha sido entregado exitosamente.',
            cancelled: 'Este pedido ha sido cancelado.',
        };
        return messages[status] || 'Estado desconocido';
    };

    const StatusIcon = order ? statusConfig[order.status].icon : Clock;
    const statusColor = order ? statusConfig[order.status].color : 'black';

    const colorClasses = {
        black: {
            bg: 'bg-black-100',
            text: 'text-black-600',
            border: 'border-black-300',
            badge: 'bg-black-100 text-black-700 border border-black-200',
        },
        yellow: {
            bg: 'bg-yellow-100',
            text: 'text-yellow-600',
            border: 'border-yellow-300',
            badge: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        },
        blue: {
            bg: 'bg-blue-100',
            text: 'text-blue-600',
            border: 'border-blue-300',
            badge: 'bg-blue-100 text-blue-700 border border-blue-200',
        },
        purple: {
            bg: 'bg-purple-100',
            text: 'text-purple-600',
            border: 'border-purple-300',
            badge: 'bg-purple-100 text-purple-700 border border-purple-200',
        },
        green: {
            bg: 'bg-green-100',
            text: 'text-green-600',
            border: 'border-green-300',
            badge: 'bg-green-100 text-green-700 border border-green-200',
        },
        red: {
            bg: 'bg-red-100',
            text: 'text-red-600',
            border: 'border-red-300',
            badge: 'bg-red-100 text-red-700 border border-red-200',
        },
    };

    return (
        <>
            <Head
                title={
                    showSearch
                        ? 'Seguimiento de Pedido'
                        : `Pedido ${order?.orderNumber}`
                }
            />

            <div className="min-h-screen bg-linear-to-br from-black-50 to-black-100 p-4 md:p-8">
                <div className="mx-auto max-w-4xl">
                    {/* Back Button */}
                    {backUrl && (
                        <motion.div
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            className="mb-4"
                        >
                            <Link
                                href={backUrl}
                                className="inline-flex items-center gap-2 text-sm font-medium text-black-600 transition-colors hover:text-black-600"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Volver
                            </Link>
                        </motion.div>
                    )}

                    {/* Header */}
                    {showSearch && (
                        <motion.div
                            initial={{ opacity: 0, y: -20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-6 text-center"
                        >
                            <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-black-700 to-black-900 shadow-lg shadow-black-500/30">
                                <Package className="h-8 w-8 text-white" />
                            </div>
                            <h1 className="mb-2 text-3xl font-bold text-black-900">
                                Seguimiento de Pedido
                            </h1>
                            <p className="text-sm text-black-600">
                                Ingresa tu número de orden para ver el estado
                            </p>
                        </motion.div>
                    )}

                    {/* Search Box */}
                    {showSearch && (
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.1 }}
                            className="mb-6 rounded-2xl bg-white p-6 shadow-xl"
                        >
                            <div className="flex flex-col gap-3 md:flex-row">
                                <div className="relative flex-1">
                                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-black-600" />
                                    <input
                                        type="text"
                                        value={orderNumber}
                                        onChange={(e) =>
                                            setOrderNumber(
                                                e.target.value.toUpperCase(),
                                            )
                                        }
                                        onKeyPress={(e) =>
                                            e.key === 'Enter' && handleSearch()
                                        }
                                        placeholder="IMP-00001"
                                        className="w-full rounded-xl border-2 border-black-200 py-3 pr-4 pl-10 font-mono text-base font-bold transition-all focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                    />
                                </div>
                                <motion.button
                                    whileHover={{
                                        scale:
                                            orderNumber && !isSearching
                                                ? 1.05
                                                : 1,
                                    }}
                                    whileTap={{
                                        scale:
                                            orderNumber && !isSearching
                                                ? 0.95
                                                : 1,
                                    }}
                                    onClick={handleSearch}
                                    disabled={!orderNumber || isSearching}
                                    className={`flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold transition-all ${
                                        orderNumber && !isSearching
                                            ? 'bg-linear-to-r from-black-700 to-black-900 text-white shadow-lg shadow-black-500/30 hover:from-black-600 hover:to-black-700'
                                            : 'cursor-not-allowed bg-black-200 text-black-400'
                                    }`}
                                >
                                    {isSearching ? (
                                        <>
                                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                            Buscando...
                                        </>
                                    ) : (
                                        <>
                                            <Search className="h-4 w-4" />
                                            Buscar
                                        </>
                                    )}
                                </motion.button>
                            </div>

                            <div className="mt-4 flex items-center justify-center gap-2 text-xs text-black-500">
                                <Lightbulb className="h-4 w-4 text-black-600" />
                                <span>
                                    El número de pedido tiene el formato:{' '}
                                    <strong className="text-black-700">
                                        IMP-00001
                                    </strong>
                                </span>
                            </div>
                        </motion.div>
                    )}

                    {/* Not Found Message */}
                    <AnimatePresence>
                        {notFound && (
                            <motion.div
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 0.9 }}
                                className="rounded-2xl bg-white p-8 text-center shadow-xl"
                            >
                                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-black-100">
                                    <AlertCircle className="h-8 w-8 text-black-600" />
                                </div>
                                <h3 className="mb-2 text-xl font-bold text-black-900">
                                    Pedido no encontrado
                                </h3>
                                <p className="mb-6 text-sm text-black-600">
                                    No pudimos encontrar un pedido con ese
                                    número. Verifica que esté correcto.
                                </p>
                                <button
                                    onClick={() => {
                                        setNotFound(false);
                                        setOrderNumber('');
                                    }}
                                    className="rounded-xl bg-linear-to-r from-black-700 to-black-900 px-6 py-2 text-sm font-bold text-white transition-all hover:from-black-600 hover:to-black-700"
                                >
                                    Intentar de nuevo
                                </button>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {/* Order Details */}
                    <AnimatePresence>
                        {order && (
                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -20 }}
                                className="space-y-5"
                            >
                                {/* Status Card */}
                                <div
                                    className={`rounded-2xl border-2 bg-white p-6 shadow-xl ${colorClasses[statusColor].border}`}
                                >
                                    <div className="mb-4 flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <div
                                                className={`rounded-xl p-3 ${colorClasses[statusColor].bg}`}
                                            >
                                                <StatusIcon
                                                    className={`h-6 w-6 ${colorClasses[statusColor].text}`}
                                                />
                                            </div>
                                            <div>
                                                <h2 className="font-mono text-xl font-bold text-black-900">
                                                    {order.orderNumber}
                                                </h2>
                                                <p className="text-xs text-black-600">
                                                    {getStatusMessage(
                                                        order.status,
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            className={`inline-flex items-center gap-2 rounded-xl px-3 py-1.5 ${colorClasses[statusColor].badge} text-xs font-bold`}
                                        >
                                            <div className="h-2 w-2 animate-pulse rounded-full bg-current"></div>
                                            {statusConfig[order.status].label}
                                        </div>
                                    </div>

                                    {/* Timeline */}
                                    {order.history &&
                                        order.history.length > 0 && (
                                            <div className="mt-6 border-t-2 border-black-100 pt-6">
                                                <h3 className="mb-4 flex items-center gap-2 text-sm font-bold text-black-900">
                                                    <Clock className="h-4 w-4 text-black-600" />
                                                    Historial
                                                </h3>
                                                <div className="space-y-3">
                                                    {order.history.map(
                                                        (item, idx) => (
                                                            <div
                                                                key={idx}
                                                                className="flex gap-3"
                                                            >
                                                                <div className="flex flex-col items-center">
                                                                    <div
                                                                        className={`flex h-7 w-7 items-center justify-center rounded-full ${
                                                                            idx ===
                                                                            0
                                                                                ? 'bg-linear-to-br from-black-700 to-black-900'
                                                                                : 'bg-black-200'
                                                                        }`}
                                                                    >
                                                                        {statusConfig[
                                                                            item
                                                                                .status
                                                                        ] &&
                                                                            React.createElement(
                                                                                statusConfig[
                                                                                    item
                                                                                        .status
                                                                                ]
                                                                                    .icon,
                                                                                {
                                                                                    className: `w-3.5 h-3.5 ${
                                                                                        idx ===
                                                                                        0
                                                                                            ? 'text-white'
                                                                                            : 'text-black-600'
                                                                                    }`,
                                                                                },
                                                                            )}
                                                                    </div>
                                                                    {idx !==
                                                                        order
                                                                            .history
                                                                            .length -
                                                                            1 && (
                                                                        <div className="my-1 h-full w-0.5 bg-black-200"></div>
                                                                    )}
                                                                </div>
                                                                <div className="flex-1 pb-3">
                                                                    <div className="flex items-center justify-between">
                                                                        <span
                                                                            className={`text-xs font-bold ${idx === 0 ? 'text-black-600' : 'text-black-900'}`}
                                                                        >
                                                                            {statusConfig[
                                                                                item
                                                                                    .status
                                                                            ]
                                                                                ?.label ||
                                                                                item.status}
                                                                        </span>
                                                                        <span className="text-xs text-black-500">
                                                                            {formatDate(
                                                                                item.timestamp,
                                                                            )}
                                                                        </span>
                                                                    </div>
                                                                    {item.comment && (
                                                                        <p className="mt-1 text-xs text-black-600">
                                                                            {
                                                                                item.comment
                                                                            }
                                                                        </p>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        ),
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                </div>

                                {/* Info Grid */}
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    {/* Contact Info */}
                                    <div className="rounded-xl bg-white p-5 shadow-lg">
                                        <h3 className="mb-3 flex items-center gap-2 text-sm font-bold text-black-900">
                                            <Mail className="h-4 w-4 text-black-600" />
                                            Contacto
                                        </h3>
                                        <div className="space-y-2 text-xs">
                                            <div>
                                                <p className="text-black-600">
                                                    Nombre
                                                </p>
                                                <p className="font-bold text-black-900">
                                                    {order.customerName}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-black-600">
                                                    Email
                                                </p>
                                                <p className="font-bold text-black-900">
                                                    {order.customerEmail}
                                                </p>
                                            </div>
                                            {order.customerPhone && (
                                                <div>
                                                    <p className="text-black-600">
                                                        Teléfono
                                                    </p>
                                                    <p className="font-bold text-black-900">
                                                        {order.customerPhone}
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Delivery Info */}
                                    <div className="rounded-xl bg-white p-5 shadow-lg">
                                        <h3 className="mb-3 flex items-center gap-2 text-sm font-bold text-black-900">
                                            {order.delivery.method ===
                                            'pickup' ? (
                                                <Package className="h-4 w-4 text-black-600" />
                                            ) : (
                                                <Truck className="h-4 w-4 text-black-600" />
                                            )}
                                            Entrega
                                        </h3>
                                        <div className="space-y-2 text-xs">
                                            <div>
                                                <p className="text-black-600">
                                                    Método
                                                </p>
                                                <p className="font-bold text-black-900">
                                                    {order.delivery.method ===
                                                    'pickup'
                                                        ? 'Recoger en tienda'
                                                        : 'Envío a domicilio'}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-black-600">
                                                    {order.delivery.method ===
                                                    'pickup'
                                                        ? 'Ubicación'
                                                        : 'Dirección'}
                                                </p>
                                                <p className="font-bold text-black-900">
                                                    {order.delivery.location}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Files Info */}
                                    <div className="rounded-xl bg-white p-5 shadow-lg">
                                        <h3 className="mb-3 flex items-center gap-2 text-sm font-bold text-black-900">
                                            <FileText className="h-4 w-4 text-black-600" />
                                            Archivos
                                        </h3>
                                        <div className="space-y-2">
                                            {order.files.map((file, idx) => (
                                                <div
                                                    key={idx}
                                                    className="flex items-center justify-between rounded-lg bg-black-50 p-2 text-xs"
                                                >
                                                    <span className="flex-1 truncate font-medium text-black-900">
                                                        {file.name}
                                                    </span>
                                                    <span className="ml-2 shrink-0 font-bold text-black-600">
                                                        {file.pages} pág.
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Print Config */}
                                    <div className="rounded-xl bg-white p-5 shadow-lg">
                                        <h3 className="mb-3 flex items-center gap-2 text-sm font-bold text-black-900">
                                            <Printer className="h-4 w-4 text-black-600" />
                                            Configuración
                                        </h3>
                                        <div className="space-y-2 text-xs">
                                            <div className="flex justify-between">
                                                <span className="text-black-600">
                                                    Tipo:
                                                </span>
                                                <span className="font-bold text-black-900">
                                                    {order.config.printType ===
                                                    'bw'
                                                        ? 'Blanco y Negro'
                                                        : 'Color'}
                                                </span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-black-600">
                                                    Tamaño:
                                                </span>
                                                <span className="font-bold text-black-900">
                                                    {order.config.paperSize.toUpperCase()}
                                                </span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-black-600">
                                                    Copias:
                                                </span>
                                                <span className="font-bold text-black-900">
                                                    {order.config.copies}
                                                </span>
                                            </div>
                                            {order.config.binding && (
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
                                </div>

                                {/* Total */}
                                <div className="rounded-2xl bg-linear-to-r from-black-700 to-black-900 p-5 text-white shadow-xl">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="mb-1 text-xs text-black-100">
                                                Total del Pedido
                                            </p>
                                            <p className="text-3xl font-bold">
                                                ${order.total.toFixed(2)}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-xs text-black-100">
                                                Fecha del pedido
                                            </p>
                                            <p className="text-sm font-bold">
                                                {formatDate(order.createdAt)}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Help Section */}
                                <div className="rounded-xl border-2 border-black-200 bg-linear-to-br from-black-50 to-slate-50 p-5">
                                    <h3 className="mb-2 text-sm font-bold text-black-900">
                                        ¿Necesitas ayuda?
                                    </h3>
                                    <p className="mb-3 text-xs text-black-600">
                                        Si tienes alguna pregunta sobre tu
                                        pedido, contáctanos:
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        <a
                                            href="tel:22222222"
                                            className="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 transition-shadow hover:shadow-md"
                                        >
                                            <Phone className="h-3.5 w-3.5 text-black-600" />
                                            <span className="text-xs font-bold text-black-900">
                                                2222-2222
                                            </span>
                                        </a>

                                        <a
                                            href="mailto:ayuda@impresiones.com"
                                            className="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 transition-shadow hover:shadow-md"
                                        >
                                            <Mail className="h-3.5 w-3.5 text-black-600" />
                                            <span className="text-xs font-bold text-black-900">
                                                ayuda@impresiones.com
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {/* Info Cards - Show only when no search results */}
                    {!order && !notFound && showSearch && (
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.3 }}
                            className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3"
                        >
                            {[
                                {
                                    icon: Package,
                                    title: 'Rastreo en Tiempo Real',
                                    desc: 'Sigue tu pedido en cada paso',
                                    color: 'black',
                                },
                                {
                                    icon: Bell,
                                    title: 'Notificaciones',
                                    desc: 'Te avisamos cuando esté listo',
                                    color: 'green',
                                },
                                {
                                    icon: MessageCircle,
                                    title: 'Soporte 24/7',
                                    desc: 'Estamos aquí para ayudarte',
                                    color: 'purple',
                                },
                            ].map((item, idx) => (
                                <motion.div
                                    key={idx}
                                    whileHover={{ scale: 1.05, y: -5 }}
                                    transition={{
                                        type: 'spring',
                                        stiffness: 300,
                                    }}
                                    className="rounded-xl bg-white p-5 text-center shadow-md transition-shadow hover:shadow-xl"
                                >
                                    <div
                                        className={`h-14 w-14 bg-${item.color}-100 mx-auto mb-3 flex items-center justify-center rounded-xl`}
                                    >
                                        <item.icon
                                            className={`text-${item.color}-600 h-7 w-7`}
                                        />
                                    </div>
                                    <h4 className="mb-1 text-sm font-bold text-black-900">
                                        {item.title}
                                    </h4>
                                    <p className="text-xs text-black-600">
                                        {item.desc}
                                    </p>
                                </motion.div>
                            ))}
                        </motion.div>
                    )}
                </div>
            </div>
        </>
    );
};

export default OrderTracking;
