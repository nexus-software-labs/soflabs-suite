import React from 'react';
import { motion } from 'framer-motion';
import { Check } from 'lucide-react';

const Stepper = ({ currentStep, steps }) => {
  return (
    <div className="mb-8">
      <div className="flex items-center justify-between max-w-3xl mx-auto">
        {steps.map((step, idx) => (
          <div key={step.id} className="flex items-center flex-1">
            <div className="flex flex-col items-center flex-1">
              {/* Circle with number or check */}
              <motion.div
                initial={false}
                animate={{
                  scale: currentStep === step.id ? 1.1 : 1,
                  backgroundColor: currentStep >= step.id ? '#2563EB' : '#E5E7EB',
                }}
                transition={{ duration: 0.3 }}
                className={`w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all ${
                  currentStep >= step.id
                    ? 'text-white shadow-lg shadow-blue-200'
                    : 'text-gray-500'
                }`}
              >
                {currentStep > step.id ? (
                  <Check className="w-5 h-5" />
                ) : (
                  step.id
                )}
              </motion.div>

              {/* Label */}
              <span className={`text-xs mt-2 font-medium hidden md:block transition-colors ${
                currentStep === step.id ? 'text-blue-600' : 'text-gray-500'
              }`}>
                {step.label}
              </span>
            </div>

            {/* Line connector */}
            {idx < steps.length - 1 && (
              <motion.div
                initial={false}
                animate={{
                  backgroundColor: currentStep > step.id ? '#2563EB' : '#E5E7EB',
                }}
                transition={{ duration: 0.3 }}
                className="h-0.5 flex-1 mx-2 mb-6"
              />
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default Stepper;