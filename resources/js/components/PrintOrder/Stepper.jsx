import { motion } from 'framer-motion';
import { CheckCircle } from 'lucide-react';

const Stepper = ({ currentStep, steps }) => {
    return (
        <div className="rounded-2xl border-2 border-gray-100 bg-white p-6 shadow-xl md:p-8">
            {/* Stepper personalizado con iconos */}
            <div className="relative flex items-center justify-between">
                {/* Línea de progreso de fondo */}
                <div
                    className="absolute top-6 right-0 left-0 h-1 rounded-full bg-gray-200"
                    style={{ zIndex: 0 }}
                >
                    <motion.div
                        className="h-full rounded-full bg-linear-to-r from-red-500 to-red-600"
                        initial={{ width: '0%' }}
                        animate={{
                            width: `${((currentStep - 1) / (steps.length - 1)) * 100}%`,
                        }}
                        transition={{ duration: 0.5, ease: 'easeInOut' }}
                    />
                </div>

                {/* Steps */}
                {steps.map((step, index) => {
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
                                initial={{ scale: 0.8, opacity: 0 }}
                                animate={{ scale: 1, opacity: 1 }}
                                transition={{ delay: index * 0.1 }}
                                whileHover={{ scale: isActive ? 1.1 : 1.05 }}
                                className={`flex h-12 w-12 cursor-default items-center justify-center rounded-full transition-all duration-300 md:h-14 md:w-14 ${
                                    isActive
                                        ? 'bg-linear-to-br from-red-500 to-red-600 text-white shadow-xl ring-4 shadow-red-500/40 ring-red-500/20'
                                        : isCompleted
                                          ? 'bg-linear-to-br from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/30'
                                          : 'bg-gray-200 text-gray-500 shadow-md'
                                } `}
                            >
                                {isCompleted ? (
                                    <motion.div
                                        initial={{ scale: 0 }}
                                        animate={{ scale: 1 }}
                                        transition={{
                                            type: 'spring',
                                            stiffness: 200,
                                        }}
                                    >
                                        <CheckCircle className="h-6 w-6 md:h-7 md:w-7" />
                                    </motion.div>
                                ) : StepIcon ? (
                                    <StepIcon className="h-6 w-6 md:h-7 md:w-7" />
                                ) : (
                                    <span className="text-lg font-bold">
                                        {step.id}
                                    </span>
                                )}
                            </motion.div>

                            {/* Labels */}
                            <motion.div
                                className="mt-3 max-w-[100px] text-center"
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: index * 0.1 + 0.2 }}
                            >
                                <p
                                    className={`truncate text-xs font-bold transition-colors md:text-sm ${
                                        isActive
                                            ? 'text-red-600'
                                            : isCompleted
                                              ? 'text-green-600'
                                              : 'text-gray-500'
                                    } `}
                                >
                                    {step.label}
                                </p>
                                {step.description && (
                                    <p className="mt-1 hidden text-xs text-gray-500 sm:block">
                                        {step.description}
                                    </p>
                                )}
                            </motion.div>

                            {/* Indicador numérico móvil */}
                            <div className="mt-1 sm:hidden">
                                <span
                                    className={`text-xs font-semibold ${isActive ? 'text-red-600' : isCompleted ? 'text-green-600' : 'text-gray-400'} `}
                                >
                                    {step.id}/{steps.length}
                                </span>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Indicador de progreso textual */}
            <div className="mt-6 border-t-2 border-gray-100 pt-4">
                <div className="flex items-center justify-between text-sm">
                    <span className="text-gray-600">
                        Paso{' '}
                        <span className="font-bold text-red-600">
                            {currentStep}
                        </span>{' '}
                        de {steps.length}
                    </span>
                    <span className="text-gray-500">
                        {Math.round((currentStep / steps.length) * 100)}%
                        completado
                    </span>
                </div>
                {/* Barra de progreso pequeña */}
                <div className="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                    <motion.div
                        className="h-full bg-linear-to-r from-red-500 to-red-600"
                        initial={{ width: '0%' }}
                        animate={{
                            width: `${(currentStep / steps.length) * 100}%`,
                        }}
                        transition={{ duration: 0.5 }}
                    />
                </div>
            </div>
        </div>
    );
};

export default Stepper;
