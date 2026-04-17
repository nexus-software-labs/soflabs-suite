// resources/js/Pages/PrintOrders/MyOrders.jsx

import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import {
    create as printOrdersCreate,
    myOrders as printOrdersMyOrders,
    show as printOrdersShow,
} from '@/routes/print-orders';
import { Head, Link, router } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { useState } from 'react';

import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    Eye,
    Filter,
    Package,
    Plus,
    Printer,
    Truck,
    X,
} from 'lucide-react';

const MyOrders = ({ orders, stats, filters }) => {
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [fromDate, setFromDate] = useState(filters.from || '');
    const [toDate, setToDate] = useState(filters.to || '');
    const [showDatePicker, setShowDatePicker] = useState(false);

    const statusConfig = {
        pending: { label: 'Pendiente', icon: Clock, color: 'black' },
        paid: {
            label: 'Pagado',
            icon: CheckCircle2,
            color: 'green',
        },
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

    const colorClasses = {
        black: { badge: 'bg-black-100 text-black-700 border border-black-200' },
        yellow: {
            badge: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        },
        blue: { badge: 'bg-blue-100 text-blue-700 border border-blue-200' },
        purple: {
            badge: 'bg-purple-100 text-purple-700 border border-purple-200',
        },
        green: { badge: 'bg-green-100 text-green-700 border border-green-200' },
        gray: { badge: 'bg-gray-100 text-gray-700 border border-gray-200' },
    };

    const handleFilter = () => {
        router.get(
            printOrdersMyOrders.url({
                query: {
                    status: statusFilter,
                    from: fromDate,
                    to: toDate,
                },
            }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const clearFilters = () => {
        setStatusFilter('all');
        setFromDate('');
        setToDate('');
        router.get(printOrdersMyOrders.url());
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-SV', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    };

    const formatDateRange = () => {
        if (fromDate && toDate) {
            return `${formatDate(fromDate)} - ${formatDate(toDate)}`;
        }
        if (fromDate) {
            return `Desde ${formatDate(fromDate)}`;
        }
        if (toDate) {
            return `Hasta ${formatDate(toDate)}`;
        }
        return 'Seleccionar fechas';
    };

    const hasActiveFilters = statusFilter !== 'all' || fromDate || toDate;

    const breadcrumbs = [
        {
            title: 'Dashboard',
            href: dashboard.url(),
        },
        {
            title: 'Mis Pedidos',
            href: printOrdersMyOrders.url(),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Mis Pedidos" />

            <div className="min-h-screen p-4 md:p-8">
                <div className="mx-auto max-w-7xl">
                    {/* Header */}
                    <motion.div
                        initial={{ opacity: 0, y: -20 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mb-6"
                    >
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="mb-1 text-3xl font-bold text-black-900">
                                    Mis Pedidos
                                </h1>
                                <p className="text-sm text-black-600">
                                    Administra y da seguimiento a todos tus
                                    pedidos
                                </p>
                            </div>
                            <Link
                                href={printOrdersCreate.url()}
                                className="action-button-mbe text-sm font-bold"
                            >
                                <Plus className="h-4 w-4" />
                                Nuevo pedido
                            </Link>
                        </div>
                    </motion.div>

                    {/* Compact Filters */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.1 }}
                        className="mb-6 rounded-xl bg-white p-4 shadow-md"
                    >
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-black-600" />
                                <span className="text-sm font-bold text-black-900">
                                    Filtros:
                                </span>
                            </div>

                            {/* Status Filter */}
                            <select
                                value={statusFilter}
                                onChange={(e) =>
                                    setStatusFilter(e.target.value)
                                }
                                className="rounded-xl border-2 border-black-200 bg-white px-4 py-2 text-sm font-medium focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                            >
                                <option value="all">Todos los estados</option>
                                <option value="pending">Pendiente</option>
                                <option value="paid">Pagado</option>
                                <option value="payment_pending">
                                    Esperando Pago
                                </option>
                                <option value="in_queue">En Cola</option>
                                <option value="printing">Imprimiendo</option>
                                <option value="ready">Listo</option>
                                <option value="delivered">Entregado</option>
                                <option value="cancelled">Cancelado</option>
                            </select>

                            {/* Date Range Picker */}
                            <div className="relative">
                                <button
                                    onClick={() =>
                                        setShowDatePicker(!showDatePicker)
                                    }
                                    className="inline-flex items-center gap-2 rounded-xl border-2 border-black-200 bg-white px-4 py-2 text-sm font-medium transition-all hover:border-black-300"
                                >
                                    <Calendar className="h-4 w-4 text-black-600" />
                                    <span className="text-black-700">
                                        {formatDateRange()}
                                    </span>
                                </button>

                                <AnimatePresence>
                                    {showDatePicker && (
                                        <>
                                            {/* Backdrop */}
                                            <motion.div
                                                initial={{ opacity: 0 }}
                                                animate={{ opacity: 1 }}
                                                exit={{ opacity: 0 }}
                                                onClick={() =>
                                                    setShowDatePicker(false)
                                                }
                                                className="fixed inset-0 z-40"
                                            />

                                            {/* Date Picker Popover */}
                                            <motion.div
                                                initial={{
                                                    opacity: 0,
                                                    scale: 0.95,
                                                    y: -10,
                                                }}
                                                animate={{
                                                    opacity: 1,
                                                    scale: 1,
                                                    y: 0,
                                                }}
                                                exit={{
                                                    opacity: 0,
                                                    scale: 0.95,
                                                    y: -10,
                                                }}
                                                className="absolute top-full left-0 z-50 mt-2 w-80 rounded-xl border-2 border-black-200 bg-white p-4 shadow-2xl"
                                            >
                                                <div className="mb-3 flex items-center justify-between">
                                                    <h3 className="text-sm font-bold text-black-900">
                                                        Rango de Fechas
                                                    </h3>
                                                    <button
                                                        onClick={() =>
                                                            setShowDatePicker(
                                                                false,
                                                            )
                                                        }
                                                        className="rounded-lg p-1 transition-colors hover:bg-black-100"
                                                    >
                                                        <X className="h-4 w-4 text-black-500" />
                                                    </button>
                                                </div>

                                                <div className="space-y-3">
                                                    <div>
                                                        <label className="mb-1 block text-xs font-bold text-black-700">
                                                            Desde
                                                        </label>
                                                        <input
                                                            type="date"
                                                            value={fromDate}
                                                            onChange={(e) =>
                                                                setFromDate(
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className="w-full rounded-lg border-2 border-black-200 px-3 py-2 text-sm focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                                        />
                                                    </div>

                                                    <div>
                                                        <label className="mb-1 block text-xs font-bold text-black-700">
                                                            Hasta
                                                        </label>
                                                        <input
                                                            type="date"
                                                            value={toDate}
                                                            onChange={(e) =>
                                                                setToDate(
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className="w-full rounded-lg border-2 border-black-200 px-3 py-2 text-sm focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                                                        />
                                                    </div>

                                                    <div className="flex gap-2 pt-2">
                                                        <button
                                                            onClick={() => {
                                                                setFromDate('');
                                                                setToDate('');
                                                                setShowDatePicker(
                                                                    false,
                                                                );
                                                            }}
                                                            className="flex-1 rounded-lg border-2 border-black-200 px-3 py-2 text-sm font-medium text-black-700 transition-colors hover:bg-black-50"
                                                        >
                                                            Limpiar
                                                        </button>
                                                        <button
                                                            onClick={() =>
                                                                setShowDatePicker(
                                                                    false,
                                                                )
                                                            }
                                                            className="action-button-mbe flex-1 cursor-pointer rounded-lg px-3 py-2 text-sm font-bold"
                                                        >
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                </div>
                                            </motion.div>
                                        </>
                                    )}
                                </AnimatePresence>
                            </div>

                            {/* Action Buttons */}
                            <button
                                onClick={handleFilter}
                                className="action-button-mbe cursor-pointer px-4 py-2 text-sm font-bold"
                            >
                                Aplicar
                            </button>

                            {hasActiveFilters && (
                                <motion.button
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    onClick={clearFilters}
                                    className="rounded-xl border-2 border-black-200 px-4 py-2 text-sm font-medium text-black-700 transition-colors hover:bg-black-50"
                                >
                                    Limpiar todo
                                </motion.button>
                            )}

                            {/* Filter indicator */}
                            {hasActiveFilters && (
                                <motion.div
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    className="ml-auto flex items-center gap-1 text-xs font-medium text-black-600"
                                >
                                    <div className="h-2 w-2 animate-pulse rounded-full bg-black-600"></div>
                                    Filtros activos
                                </motion.div>
                            )}
                        </div>
                    </motion.div>

                    {/* Table */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="overflow-hidden rounded-xl bg-white shadow-xl"
                    >
                        {orders.data.length === 0 ? (
                            <div className="p-12 text-center">
                                <motion.div
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    transition={{ delay: 0.3 }}
                                >
                                    <Package className="mx-auto mb-4 h-16 w-16 text-black-300" />
                                </motion.div>
                                <h3 className="mb-2 text-xl font-bold text-black-900">
                                    No hay pedidos
                                </h3>
                                <p className="mb-6 text-sm text-black-600">
                                    {hasActiveFilters
                                        ? 'No se encontraron pedidos con los filtros seleccionados'
                                        : 'Comienza creando tu primer pedido de impresión'}
                                </p>
                                {!hasActiveFilters && (
                                    <Link
                                        href={printOrdersCreate.url()}
                                        className="action-button-mbe px-6 py-3 text-sm font-bold"
                                    >
                                        <Plus className="h-4 w-4" />
                                        Crear Pedido
                                    </Link>
                                )}
                            </div>
                        ) : (
                            <>
                                {/* Table */}
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead className="border-b-2 border-black-200 bg-linear-to-r from-black-50 to-black-100">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Orden
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Fecha
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Estado
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Páginas
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Entrega
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Total
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-bold tracking-wider text-black-700 uppercase">
                                                    Acción
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-black-200">
                                            {orders.data.map((order, index) => {
                                                const displayStatus =
                                                    order.payment_status ===
                                                        'paid' &&
                                                    order.status === 'pending'
                                                        ? 'paid'
                                                        : order.status;
                                                const statusColor =
                                                    statusConfig[displayStatus]
                                                        ?.color ?? 'gray';

                                                return (
                                                    <motion.tr
                                                        key={order.id}
                                                        initial={{
                                                            opacity: 0,
                                                            x: -20,
                                                        }}
                                                        animate={{
                                                            opacity: 1,
                                                            x: 0,
                                                        }}
                                                        transition={{
                                                            delay: index * 0.05,
                                                        }}
                                                        className="transition-colors hover:bg-black-50"
                                                    >
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <Link
                                                                href={printOrdersShow.url(
                                                                    order.order_number,
                                                                )}
                                                                className="font-mono text-sm font-bold text-black-600 hover:text-black-700"
                                                            >
                                                                {
                                                                    order.order_number
                                                                }
                                                            </Link>
                                                        </td>
                                                        <td className="px-6 py-4 text-xs font-medium whitespace-nowrap text-black-600">
                                                            {formatDate(
                                                                order.created_at,
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span
                                                                className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold ${colorClasses[statusColor]?.badge ?? colorClasses.gray.badge}`}
                                                            >
                                                                {statusConfig[
                                                                    displayStatus
                                                                ]?.label ??
                                                                    displayStatus}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 text-sm font-bold whitespace-nowrap text-black-900">
                                                            {order.pages_count}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className="inline-flex items-center gap-1 text-xs font-medium text-black-600">
                                                                {order.delivery_method ===
                                                                'pickup' ? (
                                                                    <>
                                                                        <Package className="h-3.5 w-3.5" />
                                                                        Recoger
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <Truck className="h-3.5 w-3.5" />
                                                                        Envío
                                                                    </>
                                                                )}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className="text-sm font-bold text-black-900">
                                                                ${order.total}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 text-center whitespace-nowrap">
                                                            <Link
                                                                href={printOrdersShow.url(
                                                                    order.order_number,
                                                                )}
                                                                className="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-black-500 to-black-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-black-500/30 transition-all hover:from-black-600 hover:to-black-700"
                                                            >
                                                                <Eye className="h-3.5 w-3.5" />
                                                                Ver
                                                            </Link>
                                                        </td>
                                                    </motion.tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Pagination */}
                                {orders.links.length > 3 && (
                                    <div className="flex items-center justify-between border-t-2 border-black-200 bg-black-50 px-6 py-4">
                                        <div className="text-xs font-medium text-black-600">
                                            Mostrando{' '}
                                            <span className="font-bold text-black-900">
                                                {orders.from}
                                            </span>{' '}
                                            a{' '}
                                            <span className="font-bold text-black-900">
                                                {orders.to}
                                            </span>{' '}
                                            de{' '}
                                            <span className="font-bold text-black-900">
                                                {orders.total}
                                            </span>{' '}
                                            resultados
                                        </div>
                                        <div className="flex gap-1">
                                            {orders.links.map((link, idx) => {
                                                if (!link.url) {
                                                    return (
                                                        <span
                                                            key={idx}
                                                            className="cursor-not-allowed rounded-lg bg-black-200 px-3 py-2 text-xs font-medium text-black-400"
                                                            dangerouslySetInnerHTML={{
                                                                __html: link.label,
                                                            }}
                                                        />
                                                    );
                                                }

                                                return (
                                                    <Link
                                                        key={idx}
                                                        href={link.url}
                                                        className={`rounded-lg px-3 py-2 text-xs font-bold transition-all ${
                                                            link.active
                                                                ? 'bg-linear-to-r from-black-500 to-black-600 text-white shadow-md'
                                                                : 'border-2 border-black-200 bg-white text-black-700 hover:border-black-300'
                                                        }`}
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </motion.div>
                </div>
            </div>
        </AppLayout>
    );
};

export default MyOrders;
