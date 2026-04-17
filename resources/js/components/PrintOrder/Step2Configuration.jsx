import axios from 'axios';
import { motion } from 'framer-motion';
import {
    AlertCircle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Circle,
    DollarSign,
    Leaf,
    Loader2,
    Palette,
    Paperclip,
    Printer,
    RectangleHorizontal,
    RectangleVertical,
    Sparkles,
    TrendingDown,
} from 'lucide-react';
import { useEffect, useState } from 'react';

const Step2Configuration = ({ data, onUpdate, onNext, onBack, config }) => {
    const [printConfig, setPrintConfig] = useState(
        data.config || {
            printType: 'bw',
            paperSize: 'letter',
            paperType: 'bond',
            orientation: 'portrait',
            copies: 1,
            binding: false,
            doubleSided: false,
        },
    );

    const [fileAnalysis, setFileAnalysis] = useState(data.fileAnalysis || null);
    const [isAnalyzingFiles, setIsAnalyzingFiles] = useState(false);
    const [error, setError] = useState(null);
    const [appliedCoupon, setAppliedCoupon] = useState(data.promotion || null);

    useEffect(() => {
        if (data.files && data.files.length > 0 && !fileAnalysis) {
            analyzeFiles();
        }
    }, []);

    const analyzeFiles = async () => {
        if (!data.files || data.files.length === 0) return;
        setIsAnalyzingFiles(true);
        setError(null);

        try {
            const formData = new FormData();
            data.files.forEach((fileObj) => {
                formData.append('files[]', fileObj.file);
            });

            const response = await axios.post(
                '/api/print-config/analyze-files',
                formData,
                {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        'Content-Type': 'multipart/form-data',
                    },
                },
            );

            if (response.success === false) {
                throw new Error('Error al analizar los archivos');
            }

            const analysis = response?.data?.data;
            setFileAnalysis(analysis);
            onUpdate({ fileAnalysis: analysis });
        } catch (err) {
            console.error('Error:', err);
            setError(
                'No se pudieron analizar los archivos. Usando estimación.',
            );

            const estimatedPages = data.files.reduce((total, file) => {
                const sizeInKb = file.size / 1024;
                return total + Math.max(1, Math.ceil(sizeInKb / 50));
            }, 0);

            const fallback = {
                total_pages: estimatedPages,
                files: data.files.map((f) => ({
                    filename: f.name,
                    pages: Math.max(1, Math.ceil(f.size / 1024 / 50)),
                })),
            };

            setFileAnalysis(fallback);
            onUpdate({ fileAnalysis: fallback });
        } finally {
            setIsAnalyzingFiles(false);
        }
    };

    const calculateUnitPrice = (type, color, size, quantity) => {
        const priceRanges = config.prices[type]?.[color]?.[size] || [];

        for (const range of priceRanges) {
            if (
                quantity >= range.min &&
                (range.max === null || quantity <= range.max)
            ) {
                return range.price;
            }
        }

        return 0;
    };

    const getBindingPrice = (sheets) => {
        const bindingOptions = config.prices.binding || [];

        for (const option of bindingOptions) {
            if (sheets <= option.max_sheets) {
                return option.price;
            }
        }

        return bindingOptions[bindingOptions.length - 1]?.price || 0;
    };

    const calculatePrice = () => {
        if (!fileAnalysis) return null;

        const pages = fileAnalysis.total_pages;
        const totalQuantity = pages * printConfig.copies;

        const unitPrice = calculateUnitPrice(
            'printing',
            printConfig.printType,
            printConfig.paperSize,
            totalQuantity,
        );

        const paperTypeCost =
            config.prices.paper_type?.[printConfig.paperType] || 0;

        const finalPricePerPage = unitPrice + paperTypeCost;

        const baseSubtotal = totalQuantity * finalPricePerPage;

        const doubleSidedCost = printConfig.doubleSided
            ? pages * printConfig.copies * config.prices.double_sided
            : 0;

        const bindingCost = printConfig.binding ? getBindingPrice(pages) : 0;

        const subtotalBeforeDiscount =
            baseSubtotal + doubleSidedCost + bindingCost;

        // Calcular descuento si hay cupón aplicado
        let discountAmount = 0;
        if (appliedCoupon && appliedCoupon.applies_to === 'subtotal') {
            if (appliedCoupon.discount_type === 'percentage') {
                discountAmount =
                    subtotalBeforeDiscount *
                    (appliedCoupon.discount_value / 100);
                if (appliedCoupon.max_discount_amount) {
                    discountAmount = Math.min(
                        discountAmount,
                        appliedCoupon.max_discount_amount,
                    );
                }
            } else if (appliedCoupon.discount_type === 'fixed_amount') {
                discountAmount = Math.min(
                    appliedCoupon.discount_value,
                    subtotalBeforeDiscount,
                );
            }
        }

        const total = subtotalBeforeDiscount - discountAmount;

        return {
            base_subtotal: baseSubtotal,
            double_sided_cost: doubleSidedCost,
            binding_cost: bindingCost,
            subtotal_before_discount: subtotalBeforeDiscount,
            discount_amount: discountAmount,
            total: Math.max(0, total),
            unit_price: finalPricePerPage,
            total_quantity: totalQuantity,
            pages,
        };
    };

    const handleChange = (field, value) => {
        setPrintConfig((prev) => ({ ...prev, [field]: value }));
    };

    const handleCouponApplied = (promotion) => {
        setAppliedCoupon(promotion);
    };

    const handleCouponRemoved = () => {
        setAppliedCoupon(null);
    };

    const handleContinue = () => {
        const priceBreakdown = calculatePrice();
        onUpdate({
            config: printConfig,
            priceBreakdown,
            fileAnalysis,
            totalPages: fileAnalysis?.total_pages,
            promotion: appliedCoupon, // Guardar la promoción aplicada
        });
        onNext();
    };

    const totalPages = fileAnalysis?.total_pages || 0;
    const breakdown = calculatePrice() || {
        base_subtotal: 0,
        double_sided_cost: 0,
        binding_cost: 0,
        subtotal_before_discount: 0,
        discount_amount: 0,
        total: 0,
        unit_price: 0,
        total_quantity: 0,
        pages: 0,
    };

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
                        <Printer className="h-5 w-5 text-white" />
                    </motion.div>
                    <div>
                        <h2 className="text-2xl font-bold text-black-900">
                            Configurar Impresión
                        </h2>
                        <p className="text-sm text-black-600">
                            {isAnalyzingFiles ? (
                                <span className="flex items-center gap-2">
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Analizando archivos...
                                </span>
                            ) : (
                                `Personaliza tu pedido • ${totalPages} páginas detectadas`
                            )}
                        </p>
                    </div>
                </div>
            </div>

            {error && (
                <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="mb-4 flex items-start gap-3 rounded-xl border-2 border-yellow-200 bg-yellow-50 p-4"
                >
                    <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-yellow-600" />
                    <div>
                        <p className="text-sm font-bold text-yellow-900">
                            Advertencia
                        </p>
                        <p className="mt-1 text-xs text-yellow-700">{error}</p>
                    </div>
                </motion.div>
            )}

            <div className="">
                {fileAnalysis &&
                    fileAnalysis.files.some((f) => f.is_blueprint) && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="col-span-2 mb-2 flex items-start gap-3 rounded-xl border-2 border-blue-200 bg-blue-50 p-3"
                        >
                            <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
                            <div>
                                <p className="text-sm font-bold text-blue-900">
                                    Archivo(s) de Plano Detectado(s)
                                </p>
                                <p className="mt-1 text-xs text-blue-700">
                                    Los archivos de planos se imprimirán en
                                    tamaño grande independientemente de la
                                    configuración seleccionada aquí.
                                </p>

                                <div className="mt-2 w-full">
                                    {fileAnalysis.files
                                        .filter((f) => f.is_blueprint)
                                        .map((file, index) => (
                                            <div
                                                key={index}
                                                className="text-xs text-blue-700"
                                            >
                                                <div className="">
                                                    {file.filename}
                                                </div>
                                                <b>Tamaño: </b>{' '}
                                                {file.dimensions.width_inches} x{' '}
                                                {file.dimensions.height_inches}{' '}
                                                pulgadas{' '}
                                                {file.dimensions.page_size}
                                            </div>
                                        ))}
                                </div>
                            </div>
                        </motion.div>
                    )}
            </div>

            <div className="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label className="mb-2 block text-sm font-bold text-black-700">
                        Tipo de Impresión
                    </label>
                    <div className="grid grid-cols-2 gap-2">
                        {[
                            {
                                value: 'bw',
                                label: 'Blanco y Negro',
                                icon: Circle,
                                price: config.prices.printing.bw.letter[0]
                                    .price,
                            },
                            {
                                value: 'color',
                                label: 'Color',
                                icon: Palette,
                                price: config.prices.printing.color.letter[0]
                                    .price,
                            },
                        ].map((option) => (
                            <motion.button
                                key={option.value}
                                whileHover={{ scale: 1.03, y: -2 }}
                                whileTap={{ scale: 0.98 }}
                                onClick={() =>
                                    handleChange('printType', option.value)
                                }
                                className={`relative rounded-xl border-2 p-3 transition-all ${printConfig.printType === option.value ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-lg shadow-black-500/20' : 'border-black-200 bg-white hover:border-black-300'}`}
                            >
                                {printConfig.printType === option.value && (
                                    <motion.div
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        className="absolute top-2 right-2"
                                    >
                                        <CheckCircle2 className="h-4 w-4 text-black-600" />
                                    </motion.div>
                                )}
                                <div className="mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-black-100 to-black-200">
                                    <option.icon className="h-4 w-4 text-black-700" />
                                </div>
                                <div className="mb-1 text-sm font-bold text-black-900">
                                    {option.label}
                                </div>
                                <div className="text-xs font-semibold text-black-600">
                                    ${option.price.toFixed(2)}/página
                                </div>
                            </motion.button>
                        ))}
                    </div>
                </div>

                <div className="mb-4">
                    <label className="mb-2 block text-sm font-bold text-black-700">
                        Tamaño de Papel
                    </label>
                    <select
                        value={printConfig.paperSize}
                        onChange={(e) =>
                            handleChange('paperSize', e.target.value)
                        }
                        className="w-full rounded-xl border-2 border-black-200 bg-white p-3 text-sm font-medium text-black-900 transition-all hover:border-black-300 focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                    >
                        <option value="letter">
                            Carta (Letter) - Desde $
                            {config.prices.printing[
                                printConfig.printType
                            ].letter[0].price.toFixed(2)}
                        </option>
                        <option value="legal">
                            Legal - Desde $
                            {config.prices.printing[
                                printConfig.printType
                            ].legal[0].price.toFixed(2)}
                        </option>
                        <option value="double_letter">
                            Doble Carta - Desde $
                            {config.prices.printing[
                                printConfig.printType
                            ].double_letter[0].price.toFixed(2)}
                        </option>
                    </select>

                    <div className="mt-4">
                        <label className="mb-2 block text-sm font-bold text-black-700">
                            Tipo de Papel
                        </label>
                        <select
                            value={printConfig.paperType}
                            onChange={(e) =>
                                handleChange('paperType', e.target.value)
                            }
                            className="w-full rounded-xl border-2 border-black-200 bg-white p-3 text-sm font-medium text-black-900 transition-all hover:border-black-300 focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                        >
                            {config.prices.paper_type &&
                                Object.entries(config.prices.paper_type).map(
                                    ([key, value]) => {
                                        const labels = {
                                            bond: 'Papel Bond',
                                            photo_glossy:
                                                'Papel Fotográfico Brillante',
                                        };
                                        const additionalCost =
                                            value > 0
                                                ? ` (+$${value.toFixed(2)}/pág)`
                                                : '';
                                        return (
                                            <option key={key} value={key}>
                                                {labels[key] || key}
                                                {additionalCost}
                                            </option>
                                        );
                                    },
                                )}
                        </select>
                    </div>
                </div>

                <div>
                    <label className="mb-2 block text-sm font-bold text-black-700">
                        Orientación
                    </label>
                    <div className="grid grid-cols-2 gap-2">
                        {[
                            {
                                value: 'portrait',
                                label: 'Vertical',
                                icon: RectangleVertical,
                            },
                            {
                                value: 'landscape',
                                label: 'Horizontal',
                                icon: RectangleHorizontal,
                            },
                        ].map((option) => (
                            <motion.button
                                key={option.value}
                                whileHover={{ scale: 1.03, y: -2 }}
                                whileTap={{ scale: 0.98 }}
                                onClick={() =>
                                    handleChange('orientation', option.value)
                                }
                                className={`relative flex items-center justify-center gap-2 rounded-xl border-2 p-3 transition-all ${printConfig.orientation === option.value ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-lg shadow-black-500/20' : 'border-black-200 bg-white hover:border-black-300'}`}
                            >
                                {printConfig.orientation === option.value && (
                                    <motion.div
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        className="absolute top-2 right-2"
                                    >
                                        <CheckCircle2 className="h-3.5 w-3.5 text-black-600" />
                                    </motion.div>
                                )}
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-linear-to-br from-black-100 to-black-200">
                                    <option.icon className="h-4 w-4 text-black-700" />
                                </div>
                                <span className="text-sm font-semibold text-black-900">
                                    {option.label}
                                </span>
                            </motion.button>
                        ))}
                    </div>
                </div>

                <div>
                    <label className="mb-2 block text-sm font-bold text-black-700">
                        Número de Copias
                    </label>
                    <div className="flex items-center gap-2">
                        <motion.button
                            whileHover={{ scale: 1.1 }}
                            whileTap={{ scale: 0.9 }}
                            onClick={() =>
                                handleChange(
                                    'copies',
                                    Math.max(1, printConfig.copies - 1),
                                )
                            }
                            className="h-10 w-10 rounded-xl bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 text-lg font-bold text-white shadow-lg shadow-black-500/30 transition-all hover:from-mbe-accent-2 hover:to-mbe-secondary-2"
                        >
                            -
                        </motion.button>
                        <input
                            type="number"
                            min="1"
                            max={config.limits.max_copies}
                            value={printConfig.copies}
                            onChange={(e) =>
                                handleChange(
                                    'copies',
                                    Math.max(
                                        1,
                                        Math.min(
                                            config.limits.max_copies,
                                            parseInt(e.target.value) || 1,
                                        ),
                                    ),
                                )
                            }
                            className="flex-1 rounded-xl border-2 border-black-200 p-3 text-center text-xl font-bold text-black-900 focus:border-black-500 focus:ring-4 focus:ring-black-500/10 focus:outline-none"
                        />
                        <motion.button
                            whileHover={{ scale: 1.1 }}
                            whileTap={{ scale: 0.9 }}
                            onClick={() =>
                                handleChange(
                                    'copies',
                                    Math.min(
                                        config.limits.max_copies,
                                        printConfig.copies + 1,
                                    ),
                                )
                            }
                            className="h-10 w-10 rounded-xl bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 text-lg font-bold text-white shadow-lg shadow-black-500/30 transition-all hover:from-mbe-accent-2 hover:to-mbe-secondary-2"
                        >
                            +
                        </motion.button>
                    </div>
                    <p className="mt-1.5 text-center text-xs text-black-500">
                        Máximo {config.limits.max_copies} copias
                    </p>
                </div>

                <div className="md:col-span-2">
                    <label className="mb-2 block text-sm font-bold text-black-700">
                        Opciones Adicionales
                    </label>
                    <div className="space-y-2">
                        <motion.label
                            whileHover={{ scale: 1.01, x: 5 }}
                            className={`flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition-all ${printConfig.doubleSided ? 'border-blue-500 bg-linear-to-br from-blue-50 to-cyan-50 shadow-lg shadow-blue-500/20' : 'border-black-200 bg-white hover:border-black-300'}`}
                        >
                            <input
                                type="checkbox"
                                checked={printConfig.doubleSided}
                                onChange={(e) =>
                                    handleChange(
                                        'doubleSided',
                                        e.target.checked,
                                    )
                                }
                                className="h-5 w-5 rounded-lg border-2 text-black-600 focus:ring-4 focus:ring-black-500/20"
                            />
                            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-blue-100 to-cyan-200">
                                <Leaf className="h-4 w-4 text-blue-700" />
                            </div>
                            <div className="flex-1">
                                <div className="flex items-center gap-2 text-sm font-bold text-black-900">
                                    Impresión a Doble Cara
                                    {printConfig.doubleSided && (
                                        <CheckCircle2 className="h-4 w-4 text-blue-600" />
                                    )}
                                </div>
                                <div className="mt-0.5 text-xs text-black-600">
                                    Ahorra papel (+$
                                    {config.prices.double_sided.toFixed(2)} por
                                    página)
                                </div>
                            </div>
                            {printConfig.doubleSided && (
                                <motion.div
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    className="shrink-0 rounded-full bg-blue-600 px-2.5 py-1 text-xs font-bold text-white"
                                >
                                    +${breakdown.double_sided_cost.toFixed(2)}
                                </motion.div>
                            )}
                        </motion.label>

                        <motion.label
                            whileHover={{ scale: 1.01, x: 5 }}
                            className={`flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition-all ${printConfig.binding ? 'border-black-500 bg-linear-to-br from-black-50 to-slate-50 shadow-lg shadow-black-500/20' : 'border-black-200 bg-white hover:border-black-300'}`}
                        >
                            <input
                                type="checkbox"
                                checked={printConfig.binding}
                                onChange={(e) =>
                                    handleChange('binding', e.target.checked)
                                }
                                className="h-5 w-5 rounded-lg border-2 text-black-600 focus:ring-4 focus:ring-black-500/20"
                            />
                            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-black-100 to-slate-100">
                                <Paperclip className="h-4 w-4 text-black-700" />
                            </div>
                            <div className="flex-1">
                                <div className="flex items-center gap-2 text-sm font-bold text-black-900">
                                    Engargolado
                                    {printConfig.binding && (
                                        <CheckCircle2 className="h-4 w-4 text-black-600" />
                                    )}
                                </div>
                                <div className="mt-0.5 text-xs text-black-600">
                                    Presentación profesional y duradera (precio
                                    según hojas)
                                </div>
                            </div>
                            {printConfig.binding && breakdown && (
                                <motion.div
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    className="shrink-0 rounded-full bg-black-600 px-2.5 py-1 text-xs font-bold text-white"
                                >
                                    +${breakdown.binding_cost.toFixed(2)}
                                </motion.div>
                            )}
                        </motion.label>
                    </div>
                </div>
            </div>

            {/* Componente de cupón */}
            {/* <div className="mb-5">
        <CouponInput
          onCouponApplied={handleCouponApplied}
          onCouponRemoved={handleCouponRemoved}
          currentCoupon={appliedCoupon}
          orderData={{
            priceBreakdown: breakdown,
            delivery: data.delivery
          }}
          storeId={data.delivery?.branch_id}
          serviceType="print_order"
        />
      </div> */}

            {fileAnalysis && (
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2 }}
                    className="mt-6 rounded-2xl border-2 border-black-200 bg-linear-to-br from-black-50 via-slate-50 to-black-50 p-5 shadow-xl shadow-black-500/10"
                >
                    <div className="mb-3 flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-md">
                            <DollarSign className="h-5 w-5 text-white" />
                        </div>
                        <h3 className="text-base font-bold text-black-900">
                            Resumen de costos
                        </h3>
                    </div>
                    <div className="space-y-2">
                        <div className="flex items-center justify-between rounded-lg bg-white/60 p-2.5 backdrop-blur-sm">
                            <span className="text-sm font-medium text-black-700">
                                Impresión ({breakdown.total_quantity} páginas
                                totales)
                            </span>
                            <span className="text-sm font-bold text-black-900">
                                ${breakdown.base_subtotal.toFixed(2)}
                            </span>
                        </div>
                        {printConfig.doubleSided &&
                            breakdown.double_sided_cost > 0 && (
                                <motion.div
                                    initial={{ scale: 0.9, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    className="flex items-center justify-between rounded-lg bg-blue-50 p-2.5"
                                >
                                    <span className="flex items-center gap-1.5 text-sm font-medium text-blue-700">
                                        <Sparkles className="h-3.5 w-3.5" />
                                        Doble cara (+$
                                        {config.prices.double_sided}/pág)
                                    </span>
                                    <span className="text-sm font-bold text-blue-700">
                                        +$
                                        {breakdown.double_sided_cost.toFixed(2)}
                                    </span>
                                </motion.div>
                            )}
                        {printConfig.binding && breakdown.binding_cost > 0 && (
                            <motion.div
                                initial={{ scale: 0.9, opacity: 0 }}
                                animate={{ scale: 1, opacity: 1 }}
                                className="flex items-center justify-between rounded-lg bg-white/60 p-2.5 backdrop-blur-sm"
                            >
                                <span className="text-sm font-medium text-black-700">
                                    Engargolado ({breakdown.pages} hojas)
                                </span>
                                <span className="text-sm font-bold text-black-900">
                                    ${breakdown.binding_cost.toFixed(2)}
                                </span>
                            </motion.div>
                        )}

                        {/* Mostrar descuento si hay cupón aplicado */}
                        {appliedCoupon && breakdown.discount_amount > 0 && (
                            <motion.div
                                initial={{ scale: 0.9, opacity: 0 }}
                                animate={{ scale: 1, opacity: 1 }}
                                className="flex items-center justify-between rounded-lg border border-green-200 bg-linear-to-br from-green-50 to-emerald-50 p-2.5"
                            >
                                <span className="flex items-center gap-1.5 text-sm font-medium text-green-700">
                                    <TrendingDown className="h-3.5 w-3.5" />
                                    Descuento - {appliedCoupon.name}
                                </span>
                                <span className="text-sm font-bold text-green-700">
                                    -${breakdown.discount_amount.toFixed(2)}
                                </span>
                            </motion.div>
                        )}

                        <div className="flex items-center justify-between border-t-2 border-black-200 pt-3">
                            <span className="text-lg font-bold text-black-900">
                                Total
                            </span>
                            <motion.span
                                animate={{ scale: [1, 1.05, 1] }}
                                transition={{
                                    duration: 0.5,
                                    repeat: Infinity,
                                    repeatDelay: 2,
                                }}
                                className="text-2xl font-bold text-black-600"
                            >
                                ${breakdown.total.toFixed(2)}
                            </motion.span>
                        </div>
                    </div>
                </motion.div>
            )}

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
                    whileHover={{ scale: 1.05, x: 5 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={handleContinue}
                    disabled={isAnalyzingFiles || !fileAnalysis}
                    className={`flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold transition-all ${isAnalyzingFiles || !fileAnalysis ? 'cursor-not-allowed bg-black-300 text-black-500' : 'bg-linear-to-r from-mbe-accent to-mbe-accent-2 text-white shadow-xl shadow-black-500/30 hover:from-mbe-accent-2 hover:to-mbe-accent hover:shadow-2xl hover:shadow-black-500/40'}`}
                >
                    {isAnalyzingFiles ? (
                        <>
                            <Loader2 className="h-5 w-5 animate-spin" />
                            Analizando...
                        </>
                    ) : (
                        <>
                            Continuar
                            <motion.div
                                animate={{ x: [0, 5, 0] }}
                                transition={{ duration: 1, repeat: Infinity }}
                            >
                                <ChevronRight className="h-5 w-5" />
                            </motion.div>
                        </>
                    )}
                </motion.button>
            </div>
        </div>
    );
};

export default Step2Configuration;
