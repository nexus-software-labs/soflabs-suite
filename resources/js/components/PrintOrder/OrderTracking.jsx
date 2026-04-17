import { AnimatePresence, motion } from 'framer-motion';
import {
    AlertCircle,
    CheckCircle2,
    Clock,
    FileText,
    Mail,
    Package,
    Phone,
    Printer,
    Search,
    Truck,
} from 'lucide-react';
import { useState } from 'react';

const OrderTracking = () => {
    const [orderNumber, setOrderNumber] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [order, setOrder] = useState(null);
    const [notFound, setNotFound] = useState(false);

    // Datos simulados de una orden
    const mockOrder = {
        orderNumber: 'IMP-00123',
        status: 'printing',
        createdAt: '2025-10-20T10:30:00',
        customerName: 'Juan Pérez',
        customerEmail: 'juan@ejemplo.com',
        customerPhone: '2222-2222',
        total: 12.5,
        files: [
            { name: 'Documento.pdf', pages: 10 },
            { name: 'Presentacion.pptx', pages: 15 },
        ],
        config: {
            printType: 'color',
            paperSize: 'letter',
            copies: 2,
            binding: true,
        },
        delivery: {
            method: 'pickup',
            location: 'Centro Histórico - Av. Cuscatlán #123',
        },
        history: [
            {
                status: 'pending',
                timestamp: '2025-10-20T10:30:00',
                comment: 'Pedido recibido',
            },
            {
                status: 'payment_pending',
                timestamp: '2025-10-20T10:35:00',
                comment: 'Esperando confirmación de pago',
            },
            {
                status: 'in_queue',
                timestamp: '2025-10-20T11:00:00',
                comment: 'Pago confirmado, en cola de impresión',
            },
            {
                status: 'printing',
                timestamp: '2025-10-20T14:30:00',
                comment: 'Imprimiendo documentos',
                current: true,
            },
        ],
    };

    const statusConfig = {
        pending: { label: 'Pendiente', icon: Clock, color: 'gray' },
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
        cancelled: { label: 'Cancelado', icon: AlertCircle, color: 'red' },
    };

    const handleSearch = () => {
        setIsSearching(true);
        setNotFound(false);
        setOrder(null);

        // Simular búsqueda
        setTimeout(() => {
            if (orderNumber.toUpperCase() === 'IMP-00123') {
                setOrder(mockOrder);
                setNotFound(false);
            } else {
                setOrder(null);
                setNotFound(true);
            }
            setIsSearching(false);
        }, 1000);
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

    const StatusIcon = order ? statusConfig[order.status].icon : Clock;
    const statusColor = order ? statusConfig[order.status].color : 'gray';

    const colorClasses = {
        gray: {
            bg: 'bg-gray-100',
            text: 'text-gray-600',
            border: 'border-gray-300',
        },
        yellow: {
            bg: 'bg-yellow-100',
            text: 'text-yellow-600',
            border: 'border-yellow-300',
        },
        blue: {
            bg: 'bg-blue-100',
            text: 'text-blue-600',
            border: 'border-blue-300',
        },
        purple: {
            bg: 'bg-purple-100',
            text: 'text-purple-600',
            border: 'border-purple-300',
        },
        green: {
            bg: 'bg-green-100',
            text: 'text-green-600',
            border: 'border-green-300',
        },
        red: {
            bg: 'bg-red-100',
            text: 'text-red-600',
            border: 'border-red-300',
        },
    };

    return (
        <div className="min-h-screen bg-linear-to-br from-blue-50 via-white to-purple-50 p-4 md:p-8">
            <div className="mx-auto max-w-4xl">
                {/* Header */}
                <motion.div
                    initial={{ opacity: 0, y: -20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="mb-8 text-center"
                >
                    <h1 className="mb-2 text-4xl font-bold text-gray-900">
                        Seguimiento de Pedido
                    </h1>
                    <p className="text-gray-600">
                        Ingresa tu número de orden para ver el estado de tu
                        pedido
                    </p>
                </motion.div>

                {/* Search Box */}
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2 }}
                    className="mb-6 rounded-2xl bg-white p-8 shadow-xl"
                >
                    <div className="flex flex-col gap-4 md:flex-row">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                value={orderNumber}
                                onChange={(e) =>
                                    setOrderNumber(e.target.value.toUpperCase())
                                }
                                onKeyPress={(e) =>
                                    e.key === 'Enter' && handleSearch()
                                }
                                placeholder="IMP-00123"
                                className="w-full rounded-lg border-2 border-gray-200 py-4 pr-4 pl-10 font-mono text-lg transition-colors focus:border-blue-600 focus:outline-none"
                            />
                        </div>
                        <motion.button
                            whileHover={{ scale: 1.05 }}
                            whileTap={{ scale: 0.95 }}
                            onClick={handleSearch}
                            disabled={!orderNumber || isSearching}
                            className={`flex items-center justify-center gap-2 rounded-lg px-8 py-4 font-medium transition-all ${
                                orderNumber && !isSearching
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700'
                                    : 'cursor-not-allowed bg-gray-300 text-gray-500'
                            }`}
                        >
                            {isSearching ? (
                                <>
                                    <div className="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                    Buscando...
                                </>
                            ) : (
                                <>
                                    <Search className="h-5 w-5" />
                                    Buscar
                                </>
                            )}
                        </motion.button>
                    </div>

                    <p className="mt-4 text-center text-sm text-gray-500">
                        💡 Prueba con:{' '}
                        <span className="font-mono font-semibold text-blue-600">
                            IMP-00123
                        </span>
                    </p>
                </motion.div>

                {/* Not Found Message */}
                <AnimatePresence>
                    {notFound && (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9 }}
                            className="mb-6 rounded-xl border-2 border-red-200 bg-red-50 p-6"
                        >
                            <div className="flex items-center gap-3">
                                <AlertCircle className="h-6 w-6 shrink-0 text-red-600" />
                                <div>
                                    <h3 className="font-semibold text-red-900">
                                        Pedido no encontrado
                                    </h3>
                                    <p className="text-sm text-red-700">
                                        No se encontró un pedido con el número{' '}
                                        <span className="font-mono font-semibold">
                                            {orderNumber}
                                        </span>
                                        . Verifica que lo hayas escrito
                                        correctamente.
                                    </p>
                                </div>
                            </div>
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
                            className="space-y-6"
                        >
                            {/* Status Card */}
                            <div
                                className={`rounded-2xl border-2 bg-white p-8 shadow-xl ${colorClasses[statusColor].border}`}
                            >
                                <div className="mb-6 flex items-center justify-between">
                                    <div>
                                        <p className="mb-1 text-sm text-gray-600">
                                            Número de Pedido
                                        </p>
                                        <h2 className="font-mono text-2xl font-bold text-gray-900">
                                            {order.orderNumber}
                                        </h2>
                                    </div>
                                    <div
                                        className={`rounded-xl p-4 ${colorClasses[statusColor].bg}`}
                                    >
                                        <StatusIcon
                                            className={`h-8 w-8 ${colorClasses[statusColor].text}`}
                                        />
                                    </div>
                                </div>

                                <div
                                    className={`inline-flex items-center gap-2 rounded-full px-4 py-2 ${colorClasses[statusColor].bg} ${colorClasses[statusColor].text} font-semibold`}
                                >
                                    <div className="h-2 w-2 animate-pulse rounded-full bg-current"></div>
                                    {statusConfig[order.status].label}
                                </div>

                                <p className="mt-4 text-gray-600">
                                    {order.status === 'pending' &&
                                        'Tu pedido ha sido recibido y está siendo procesado.'}
                                    {order.status === 'payment_pending' &&
                                        'Estamos esperando la confirmación de tu pago.'}
                                    {order.status === 'in_queue' &&
                                        'Tu pedido está en cola de impresión. Pronto comenzaremos a imprimirlo.'}
                                    {order.status === 'printing' &&
                                        'Tu pedido está siendo impreso en este momento.'}
                                    {order.status === 'ready' &&
                                        'Tu pedido está listo para ser recogido en la ubicación seleccionada.'}
                                    {order.status === 'shipped' &&
                                        'Tu pedido está en camino a la dirección de entrega.'}
                                    {order.status === 'delivered' &&
                                        'Tu pedido ha sido entregado exitosamente.'}
                                    {order.status === 'cancelled' &&
                                        'Este pedido ha sido cancelado.'}
                                </p>
                            </div>

                            {/* Timeline */}
                            <div className="rounded-2xl bg-white p-8 shadow-xl">
                                <h3 className="mb-6 flex items-center gap-2 font-bold text-gray-900">
                                    <Clock className="h-5 w-5 text-blue-600" />
                                    Historial del Pedido
                                </h3>
                                <div className="space-y-4">
                                    {order.history.map((item, idx) => {
                                        const ItemIcon =
                                            statusConfig[item.status].icon;
                                        const itemColor =
                                            statusConfig[item.status].color;
                                        const isLast =
                                            idx === order.history.length - 1;

                                        return (
                                            <div
                                                key={idx}
                                                className="flex gap-4"
                                            >
                                                <div className="flex flex-col items-center">
                                                    <div
                                                        className={`h-10 w-10 rounded-full ${colorClasses[itemColor].bg} flex shrink-0 items-center justify-center`}
                                                    >
                                                        <ItemIcon
                                                            className={`h-5 w-5 ${colorClasses[itemColor].text}`}
                                                        />
                                                    </div>
                                                    {!isLast && (
                                                        <div className="my-1 h-full min-h-[40px] w-0.5 bg-gray-200"></div>
                                                    )}
                                                </div>
                                                <div className="flex-1 pb-6">
                                                    <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                        <div>
                                                            <p className="font-semibold text-gray-900">
                                                                {
                                                                    statusConfig[
                                                                        item
                                                                            .status
                                                                    ].label
                                                                }
                                                            </p>
                                                            <p className="mt-1 text-sm text-gray-600">
                                                                {item.comment}
                                                            </p>
                                                        </div>
                                                        <span className="text-xs whitespace-nowrap text-gray-500">
                                                            {formatDate(
                                                                item.timestamp,
                                                            )}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Order Details Grid */}
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {/* Customer Info */}
                                <div className="rounded-xl bg-white p-6 shadow-lg">
                                    <h3 className="mb-4 flex items-center gap-2 font-bold text-gray-900">
                                        <Mail className="h-5 w-5 text-blue-600" />
                                        Información de Contacto
                                    </h3>
                                    <div className="space-y-3 text-sm">
                                        <div>
                                            <p className="text-gray-600">
                                                Nombre
                                            </p>
                                            <p className="font-semibold text-gray-900">
                                                {order.customerName}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-gray-600">
                                                Email
                                            </p>
                                            <p className="font-semibold text-gray-900">
                                                {order.customerEmail}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-gray-600">
                                                Teléfono
                                            </p>
                                            <p className="font-semibold text-gray-900">
                                                {order.customerPhone}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Delivery Info */}
                                <div className="rounded-xl bg-white p-6 shadow-lg">
                                    <h3 className="mb-4 flex items-center gap-2 font-bold text-gray-900">
                                        {order.delivery.method === 'pickup' ? (
                                            <Package className="h-5 w-5 text-blue-600" />
                                        ) : (
                                            <Truck className="h-5 w-5 text-blue-600" />
                                        )}
                                        Entrega
                                    </h3>
                                    <div className="space-y-3 text-sm">
                                        <div>
                                            <p className="text-gray-600">
                                                Método
                                            </p>
                                            <p className="font-semibold text-gray-900">
                                                {order.delivery.method ===
                                                'pickup'
                                                    ? 'Recoger en tienda'
                                                    : 'Envío a domicilio'}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-gray-600">
                                                {order.delivery.method ===
                                                'pickup'
                                                    ? 'Ubicación'
                                                    : 'Dirección'}
                                            </p>
                                            <p className="font-semibold text-gray-900">
                                                {order.delivery.location}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Files Info */}
                                <div className="rounded-xl bg-white p-6 shadow-lg">
                                    <h3 className="mb-4 flex items-center gap-2 font-bold text-gray-900">
                                        <FileText className="h-5 w-5 text-blue-600" />
                                        Archivos
                                    </h3>
                                    <div className="space-y-2">
                                        {order.files.map((file, idx) => (
                                            <div
                                                key={idx}
                                                className="flex items-center justify-between rounded bg-gray-50 p-2 text-sm"
                                            >
                                                <span className="flex-1 truncate text-gray-900">
                                                    {file.name}
                                                </span>
                                                <span className="ml-2 shrink-0 text-gray-600">
                                                    {file.pages} pág.
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Print Config */}
                                <div className="rounded-xl bg-white p-6 shadow-lg">
                                    <h3 className="mb-4 flex items-center gap-2 font-bold text-gray-900">
                                        <Printer className="h-5 w-5 text-blue-600" />
                                        Configuración
                                    </h3>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">
                                                Tipo:
                                            </span>
                                            <span className="font-semibold text-gray-900">
                                                {order.config.printType === 'bw'
                                                    ? 'Blanco y Negro'
                                                    : 'Color'}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">
                                                Tamaño:
                                            </span>
                                            <span className="font-semibold text-gray-900">
                                                {order.config.paperSize.toUpperCase()}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">
                                                Copias:
                                            </span>
                                            <span className="font-semibold text-gray-900">
                                                {order.config.copies}
                                            </span>
                                        </div>
                                        {order.config.binding && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">
                                                    Engargolado:
                                                </span>
                                                <span className="font-semibold text-blue-600">
                                                    Sí
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Total */}
                            <div className="rounded-2xl bg-linear-to-r from-blue-600 to-purple-600 p-6 text-white shadow-xl">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="mb-1 text-blue-100">
                                            Total del Pedido
                                        </p>
                                        <p className="text-4xl font-bold">
                                            ${order.total.toFixed(2)}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm text-blue-100">
                                            Fecha del pedido
                                        </p>
                                        <p className="font-semibold">
                                            {formatDate(order.createdAt)}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Help Section */}
                            <div className="rounded-xl border-2 border-blue-200 bg-blue-50 p-6">
                                <h3 className="mb-3 font-semibold text-gray-900">
                                    ¿Necesitas ayuda?
                                </h3>
                                <p className="mb-4 text-sm text-gray-600">
                                    Si tienes alguna pregunta sobre tu pedido,
                                    contáctanos:
                                </p>
                                <div className="flex flex-wrap gap-4">
                                    <a
                                        href="tel:22222222"
                                        className="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 transition-shadow hover:shadow-md"
                                    >
                                        <Phone className="h-4 w-4 text-blue-600" />
                                        <span className="text-sm font-medium text-gray-900">
                                            2222-2222
                                        </span>
                                    </a>
                                    <a
                                        href="mailto:ayuda@impresiones.com"
                                        className="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 transition-shadow hover:shadow-md"
                                    >
                                        <Mail className="h-4 w-4 text-blue-600" />
                                        <span className="text-sm font-medium text-gray-900">
                                            ayuda@impresiones.com
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>

                {/* Info Cards - Show only when no search results */}
                {!order && !notFound && (
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.4 }}
                        className="mt-8 grid grid-cols-1 gap-4 md:grid-cols-3"
                    >
                        {[
                            {
                                icon: '📦',
                                title: 'Rastreo en Tiempo Real',
                                desc: 'Sigue tu pedido en cada paso',
                            },
                            {
                                icon: '🔔',
                                title: 'Notificaciones',
                                desc: 'Te avisamos cuando esté listo',
                            },
                            {
                                icon: '💬',
                                title: 'Soporte 24/7',
                                desc: 'Estamos aquí para ayudarte',
                            },
                        ].map((item, idx) => (
                            <div
                                key={idx}
                                className="rounded-xl bg-white p-6 text-center shadow-md"
                            >
                                <div className="mb-3 text-4xl">{item.icon}</div>
                                <h4 className="mb-2 font-semibold text-gray-900">
                                    {item.title}
                                </h4>
                                <p className="text-sm text-gray-600">
                                    {item.desc}
                                </p>
                            </div>
                        ))}
                    </motion.div>
                )}
            </div>
        </div>
    );
};

export default OrderTracking;
