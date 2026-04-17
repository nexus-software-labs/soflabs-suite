import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import React, { useState } from 'react';
import { route } from 'ziggy-js';

import Step1UploadFiles from '@/components/PrintOrder/Step1UploadFiles';
import Step2Configuration from '@/components/PrintOrder/Step2Configuration';
import Step3Delivery from '@/components/PrintOrder/Step3Delivery';
import Step4Confirmation from '@/components/PrintOrder/Step4Confirmation';
import Step5payment from '@/components/PrintOrder/Step5payment';
import PromotionBanner from '@/components/PromotionBanner';
import VerificationPendingModal from '@/components/VerificationPendingModal';

import {
    AlertCircle,
    CheckCircle,
    DollarSign,
    FileText,
    Printer,
    Settings,
    ShieldCheck,
    Sparkles,
    Truck,
    Zap,
} from 'lucide-react';

const CreatePrintOrder = ({
    auth,
    branches,
    customerAddresses,
    suggestedBranchId,
    config,
    errors,
    showVerificationModal = false,
    isVerified = true,
}) => {
    const [currentStep, setCurrentStep] = useState(1);
    const [isSubmitting, setIsSubmitting] = useState(false);
    // Solo mostrar modal si hay usuario autenticado y no está verificado
    const [showModal, setShowModal] = useState(
        auth?.user && showVerificationModal,
    );

    const [orderData, setOrderData] = useState({
        files: [],
        fileAnalysis: null,
        config: {
            printType: 'bw',
            paperSize: 'letter',
            paperType: 'bond',
            orientation: 'portrait',
            copies: 1,
            binding: false,
            doubleSided: false,
            pageRange: 'all',
        },
        delivery: {
            method: 'pickup',
            branch_id: suggestedBranchId,
            customerAddressId: null,
            address: auth?.user?.address || '',
            phone: auth?.user?.phone || '',
            notes: '',
        },
        customer: {
            name: auth?.user?.name || '',
            email: auth?.user?.email || '',
            phone: auth?.user?.customer?.phone || '',
            notes: '',
        },
        priceBreakdown: {
            base_subtotal: 0,
            double_sided_cost: 0,
            binding_cost: 0,
            total: 0,
            unit_price: 0,
            total_quantity: 0,
            pages: 0,
        },
        // 🎯 Promociones separadas
        deliveryPromotion: null, // Promoción automática de delivery (Step3)
        generalPromotion: null, // Cupón general (Step4)
    });

    const steps = [
        {
            id: 1,
            label: 'Archivos',
            icon: FileText,
            description: 'Sube tus documentos',
        },
        {
            id: 2,
            label: 'Configurar',
            icon: Settings,
            description: 'Opciones de impresión',
        },
        {
            id: 3,
            label: 'Entrega',
            icon: Truck,
            description: 'Método de entrega',
        },
        {
            id: 4,
            label: 'Confirmar',
            icon: CheckCircle,
            description: 'Revisar pedido',
        },
        {
            id: 5,
            label: 'Pago',
            icon: DollarSign,
            description: 'Completa tu pago',
        },
    ];

    const breadcrumbs = [
        {
            title: 'Mis Pedidos',
            href: route('print-orders.my-orders'),
        },
        {
            title: 'Crear Pedido',
            href: route('print-orders.create'),
        },
    ];
    const isAuthenticated = !!auth?.user;
    const Wrapper = isAuthenticated ? AppLayout : React.Fragment;
    const wrapperProps = isAuthenticated ? { breadcrumbs } : {};

    const handleUpdateData = (newData) => {
        setOrderData((prev) => ({ ...prev, ...newData }));
    };

    const handleNext = () => {
        if (currentStep < 5) {
            setCurrentStep(currentStep + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const handleBack = () => {
        if (currentStep > 1) {
            setCurrentStep(currentStep - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const handleSubmit = async () => {
        // Solo verificar si hay usuario autenticado
        if (auth?.user && !isVerified) {
            setShowModal(true);
            return;
        }

        setIsSubmitting(true);

        const formData = new FormData();

        orderData.files.forEach((fileObj, index) => {
            formData.append(`files[${index}]`, fileObj.file);
        });

        Object.keys(orderData.config).forEach((key) => {
            const value = orderData.config[key];

            if (typeof value === 'boolean') {
                formData.append(`config[${key}]`, value ? '1' : '0');
            } else {
                formData.append(`config[${key}]`, value);
            }
        });

        Object.keys(orderData.delivery).forEach((key) => {
            if (
                orderData.delivery[key] !== null &&
                orderData.delivery[key] !== ''
            ) {
                formData.append(`delivery[${key}]`, orderData.delivery[key]);
            }
        });

        Object.keys(orderData.customer).forEach((key) => {
            if (
                orderData.customer[key] !== null &&
                orderData.customer[key] !== ''
            ) {
                formData.append(`customer[${key}]`, orderData.customer[key]);
            }
        });

        // 🎯 Enviar promoción de delivery si existe
        if (orderData.deliveryPromotion) {
            formData.append(
                'delivery_promotion_id',
                orderData.deliveryPromotion.id,
            );
            if (orderData.deliveryPromotion.coupon_code) {
                formData.append(
                    'delivery_coupon_code',
                    orderData.deliveryPromotion.coupon_code,
                );
            }
        }

        // 🎯 Enviar cupón general si existe
        if (orderData.generalPromotion) {
            formData.append(
                'general_promotion_id',
                orderData.generalPromotion.id,
            );
            if (orderData.generalPromotion.coupon_code) {
                formData.append(
                    'general_coupon_code',
                    orderData.generalPromotion.coupon_code,
                );
            }
        }

        // 🎯 Enviar costos finales calculados
        formData.append('final_delivery_cost', orderData.deliveryCost || 0);
        formData.append(
            'final_total',
            orderData.finalTotal || orderData.priceBreakdown.total,
        );

        router.post(route('print-orders.store'), formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                console.log('Pedido creado exitosamente');
            },
            onError: (errors) => {
                console.error('Errores de validación:', errors);
                setIsSubmitting(false);
                if (errors) {
                    alert(
                        'Hay errores en el formulario. Por favor revisa los datos.',
                    );
                }
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    const renderStep = () => {
        switch (currentStep) {
            case 1:
                return (
                    <Step1UploadFiles
                        data={orderData}
                        onUpdate={handleUpdateData}
                        onNext={handleNext}
                        config={config}
                    />
                );
            case 2:
                return (
                    <Step2Configuration
                        data={orderData}
                        onUpdate={handleUpdateData}
                        onNext={handleNext}
                        onBack={handleBack}
                        config={config}
                    />
                );
            case 3:
                return (
                    <Step3Delivery
                        data={orderData}
                        onUpdate={handleUpdateData}
                        onNext={handleNext}
                        onBack={handleBack}
                        branches={branches}
                        customerAddresses={customerAddresses}
                        config={config}
                        auth={auth}
                    />
                );
            case 4:
                return (
                    <Step4Confirmation
                        data={orderData}
                        onUpdate={handleUpdateData}
                        onBack={handleBack}
                        onNext={handleNext}
                        config={config}
                    />
                );
            case 5:
                return (
                    <Step5payment
                        data={orderData}
                        onBack={handleBack}
                        onSubmit={handleSubmit}
                        isSubmitting={isSubmitting}
                        orderNumber={config?.orderNumber || ''}
                        total={
                            orderData.finalTotal ||
                            orderData.priceBreakdown?.total ||
                            0
                        }
                    />
                );

            default:
                return null;
        }
    };

    // 🎯 Determinar promoción para el banner (solo automáticas)
    const displayedPromotion = orderData.deliveryPromotion;

    return (
        <Wrapper {...wrapperProps}>
            <Head title="Crear Pedido de impresión" />
            {auth?.user && (
                <VerificationPendingModal
                    isOpen={showModal}
                    onClose={() => setShowModal(false)}
                />
            )}

            <div className="min-h-screen bg-linear-to-br from-black-50 via-white to-black-50 py-8 md:py-12">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <motion.div
                        initial={{ opacity: 0, y: -20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="relative mb-6 overflow-hidden rounded-2xl border border-gray-700/50 bg-linear-to-br from-[#151414] to-[#151414] p-6 text-white shadow-2xl shadow-gray-900/50 md:p-8"
                    >
                        {/* <div className="absolute inset-0 opacity-5">
                <div className="absolute top-0 left-0 w-48 h-48 bg-red-400 rounded-full blur-3xl"></div>
                <div className="absolute bottom-0 right-0 w-64 h-64 bg-red-500 rounded-full blur-3xl"></div>
            </div> */}

                        <div className="absolute inset-0 bg-linear-to-tr from-transparent via-white/5 to-transparent"></div>

                        <div className="relative z-10">
                            <div className="mb-3 flex items-center gap-3">
                                <motion.div
                                    animate={{
                                        scale: [1, 1.1, 1],
                                        rotate: [0, 5, -5, 0],
                                    }}
                                    transition={{
                                        duration: 2,
                                        repeat: Infinity,
                                        repeatDelay: 3,
                                    }}
                                    className="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-red-500/30 bg-white/10 shadow-lg shadow-red-500/20 backdrop-blur-sm"
                                >
                                    <Printer className="h-6 w-6 text-red-400" />
                                </motion.div>
                                <div>
                                    <h1 className="text-2xl font-bold text-white md:text-3xl">
                                        Pedido de Impresión
                                    </h1>
                                    <p className="mt-0.5 text-sm text-gray-300">
                                        {auth?.user
                                            ? `Hola ${auth.user.name}! `
                                            : ''}
                                        Crea tu pedido en 5 simples pasos
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2">
                                <div className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 backdrop-blur-sm transition-colors hover:bg-white/15">
                                    <Sparkles className="h-3.5 w-3.5 text-red-400" />
                                    <span className="text-xs font-bold">
                                        Proceso rápido
                                    </span>
                                </div>
                                <div className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 backdrop-blur-sm transition-colors hover:bg-white/15">
                                    <ShieldCheck className="h-3.5 w-3.5 text-emerald-400" />
                                    <span className="text-xs font-bold">
                                        Calidad garantizada
                                    </span>
                                </div>
                                <div className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 backdrop-blur-sm transition-colors hover:bg-white/15">
                                    <Zap className="h-3.5 w-3.5 text-red-400" />
                                    <span className="text-xs font-bold">
                                        Entrega inmediata
                                    </span>
                                </div>
                            </div>
                        </div>
                    </motion.div>

                    {/* 🎯 Banner de promoción automática (solo si existe) */}
                    {displayedPromotion && (
                        <div className="mb-6">
                            <PromotionBanner promotion={displayedPromotion} />
                        </div>
                    )}

                    {errors && Object.keys(errors).length > 0 && (
                        <motion.div
                            initial={{ opacity: 0, y: -20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-6 rounded-2xl border-2 border-red-200 bg-linear-to-br from-red-50 to-orange-50 p-5 shadow-lg"
                        >
                            <div className="flex items-start gap-3">
                                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500">
                                    <AlertCircle className="h-5 w-5 text-white" />
                                </div>
                                <div className="flex-1">
                                    <h3 className="mb-2 text-base font-bold text-red-900">
                                        Errores en el formulario
                                    </h3>
                                    <ul className="space-y-1.5">
                                        {Object.values(errors).map(
                                            (error, idx) => (
                                                <li
                                                    key={idx}
                                                    className="flex items-center gap-2 text-xs text-red-700"
                                                >
                                                    <div className="h-1.5 w-1.5 rounded-full bg-red-500"></div>
                                                    {error}
                                                </li>
                                            ),
                                        )}
                                    </ul>
                                </div>
                            </div>
                        </motion.div>
                    )}

                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.1, duration: 0.4 }}
                        className="mb-6"
                    >
                        <div className="rounded-2xl border-2 border-gray-100 bg-white p-5 shadow-xl md:p-6">
                            <div className="relative flex items-center justify-between">
                                <div
                                    className="absolute top-5 right-0 left-0 h-1 rounded-full bg-gray-200"
                                    style={{ zIndex: 0 }}
                                >
                                    <motion.div
                                        className="h-full rounded-full bg-linear-to-r from-black-500 to-black-600"
                                        initial={{ width: '0%' }}
                                        animate={{
                                            width: `${((currentStep - 1) / (steps.length - 1)) * 100}%`,
                                        }}
                                        transition={{ duration: 0.5 }}
                                    />
                                </div>

                                {steps.map((step) => {
                                    const StepIcon = step.icon;
                                    const isActive = currentStep === step.id;
                                    const isCompleted = currentStep > step.id;

                                    return (
                                        <div
                                            key={step.id}
                                            className="relative flex flex-col items-center"
                                            style={{ zIndex: 1, flex: 1 }}
                                        >
                                            <motion.div
                                                whileHover={{
                                                    scale: isActive
                                                        ? 1.1
                                                        : 1.05,
                                                }}
                                                className={`flex h-10 w-10 items-center justify-center rounded-full transition-all duration-300 ${
                                                    isActive
                                                        ? 'bg-linear-to-br from-mbe-secondary-2 to-mbe-secondary-2 text-white shadow-xl ring-4 shadow-gray-500/40 ring-gray-500/20'
                                                        : isCompleted
                                                          ? 'bg-linear-to-br from-green-500 to-emerald-500 text-white shadow-lg'
                                                          : 'bg-gray-200 text-gray-500'
                                                } `}
                                            >
                                                {isCompleted ? (
                                                    <CheckCircle className="h-5 w-5" />
                                                ) : (
                                                    <StepIcon className="h-5 w-5" />
                                                )}
                                            </motion.div>
                                            <div className="mt-2 text-center">
                                                <p
                                                    className={`text-xs font-bold transition-colors ${isActive ? 'text-gray-600' : isCompleted ? 'text-green-600' : 'text-gray-500'} `}
                                                >
                                                    {step.label}
                                                </p>
                                                <p className="mt-0.5 hidden text-xs text-gray-500 sm:block">
                                                    {step.description}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </motion.div>

                    <motion.div
                        key={currentStep}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -20 }}
                        transition={{ duration: 0.3 }}
                        className="mb-6 rounded-2xl border-2 border-black-100 bg-white p-6 shadow-2xl md:p-8"
                    >
                        {renderStep()}
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="grid grid-cols-1 gap-4 md:grid-cols-3"
                    >
                        {[
                            {
                                icon: Zap,
                                title: 'Rápido y Fácil',
                                desc: 'Tu pedido listo en minutos',
                                bgGradient: 'from-[#F5F5F5] to-[#F5F5F5]',
                                iconBg: 'bg-mbe-secondary-2',
                                iconColor: 'text-[#fff]',
                            },
                            {
                                icon: DollarSign,
                                title: 'Precios Justos',
                                desc: 'Desde $0.10 por página',
                                bgGradient: 'from-[#F5F5F5] to-[#F5F5F5]',
                                iconBg: 'bg-mbe-secondary-2',
                                iconColor: 'text-[#fff]',
                            },
                            {
                                icon: ShieldCheck,
                                title: 'Calidad Premium',
                                desc: 'Impresión profesional garantizada',
                                bgGradient: 'from-[#F5F5F5] to-[#F5F5F5]',
                                iconBg: 'bg-mbe-secondary-2',
                                iconColor: 'text-[#fff]',
                            },
                        ].map((item, idx) => (
                            <motion.div
                                key={idx}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.3 + idx * 0.1 }}
                                whileHover={{
                                    scale: 1.05,
                                    y: -5,
                                    transition: { duration: 0.2 },
                                }}
                                className={`bg-linear-to-br ${item.bgGradient} group cursor-default rounded-xl border-2 border-black-100 p-5 shadow-lg transition-all duration-300 hover:shadow-xl`}
                            >
                                <div
                                    className={`h-12 w-12 ${item.iconBg} mb-3 flex items-center justify-center rounded-xl shadow-md transition-transform duration-300 group-hover:scale-110`}
                                >
                                    <item.icon
                                        className={`${item.iconColor} h-6 w-6`}
                                    />
                                </div>
                                <h4 className="mb-1 text-base font-bold text-black-900">
                                    {item.title}
                                </h4>
                                <p className="text-xs leading-relaxed text-black-600">
                                    {item.desc}
                                </p>
                            </motion.div>
                        ))}
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 0.5 }}
                        className="mt-6 text-center"
                    >
                        <p className="flex items-center justify-center gap-2 text-xs text-black-500">
                            <ShieldCheck className="h-4 w-4 text-green-600" />
                            Tus archivos están seguros y protegidos
                        </p>
                    </motion.div>
                </div>
            </div>
        </Wrapper>
    );
};

export default CreatePrintOrder;
