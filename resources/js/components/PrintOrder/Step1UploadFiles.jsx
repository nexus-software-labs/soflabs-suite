import { AnimatePresence, motion } from 'framer-motion';
import {
    AlertCircle,
    CheckCircle2,
    ChevronRight,
    File,
    FileText,
    Image,
    Lightbulb,
    Sparkles,
    Upload,
    X,
} from 'lucide-react';
import { useState } from 'react';

const Step1UploadFiles = ({ data, onUpdate, onNext, config }) => {
    const [files, setFiles] = useState(data.files || []);
    const [isDragging, setIsDragging] = useState(false);
    const [errors, setErrors] = useState([]);

    const limits = {
        maxFileSize: config.limits.max_file_size_mb * 1024 * 1024,
        maxFiles: config.limits.max_files_per_order,
        allowedTypes: config.allowed_file_types || [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
        ],
        allowedExtensions: ['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png'],
    };

    const getFileExtension = (fileName) => {
        const i = fileName?.lastIndexOf('.');
        return i >= 0 ? fileName.substring(i).toLowerCase() : '';
    };

    const validateFile = (file) => {
        const errors = [];
        const ext = getFileExtension(file.name);
        const validByMime = limits.allowedTypes.includes(file.type);
        const validByExt = limits.allowedExtensions.includes(ext);
        if (!validByMime && !validByExt) {
            errors.push(
                'Solo se permiten archivos PDF, Word (DOC/DOCX) e imágenes (JPG/PNG)',
            );
        }
        if (file.size > limits.maxFileSize) {
            errors.push(
                `El archivo excede el tamaño máximo de ${config.limits.max_file_size_mb}MB`,
            );
        }
        return errors;
    };

    const addFiles = (newFiles) => {
        const currentCount = files.length;
        const availableSlots = limits.maxFiles - currentCount;

        if (availableSlots === 0) {
            setErrors([`Máximo ${limits.maxFiles} archivos permitidos`]);
            setTimeout(() => setErrors([]), 3000);
            return;
        }

        const filesToAdd = newFiles.slice(0, availableSlots);
        const validatedFiles = filesToAdd.map((file) => ({
            id: Math.random().toString(36).substr(2, 9),
            file,
            name: file.name,
            size: file.size,
            type: file.type,
            errors: validateFile(file),
            preview: file.type.startsWith('image/')
                ? URL.createObjectURL(file)
                : null,
        }));

        const updatedFiles = [...files, ...validatedFiles];
        setFiles(updatedFiles);
        onUpdate({ files: updatedFiles });

        if (newFiles.length > availableSlots) {
            setErrors([
                `Solo se pueden agregar ${availableSlots} archivos más`,
            ]);
            setTimeout(() => setErrors([]), 3000);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setIsDragging(false);
        const droppedFiles = Array.from(e.dataTransfer.files);
        addFiles(droppedFiles);
    };

    const handleFileInput = (e) => {
        const selectedFiles = Array.from(e.target.files);
        addFiles(selectedFiles);
    };

    const removeFile = (id) => {
        const updatedFiles = files.filter((f) => f.id !== id);
        setFiles(updatedFiles);
        onUpdate({ files: updatedFiles });
    };

    const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return (
            Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
        );
    };

    const getFileIcon = (type) => {
        if (type.startsWith('image/')) return <Image className="h-5 w-5" />;
        return <FileText className="h-5 w-5" />;
    };

    const totalSize = files.reduce((acc, f) => acc + f.size, 0);
    const hasErrors = files.some((f) => f.errors.length > 0);
    const canContinue = files.length > 0 && !hasErrors;

    return (
        <div>
            {/* Header */}
            <div className="mb-6">
                <div className="mb-2 flex items-center gap-2">
                    <motion.div
                        animate={{
                            rotate: [0, 5, -5, 0],
                            scale: [1, 1.1, 1],
                        }}
                        transition={{
                            duration: 2,
                            repeat: Infinity,
                            repeatDelay: 3,
                        }}
                        className="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-lg shadow-gray-500/30"
                    >
                        <Upload className="h-5 w-5 text-white" />
                    </motion.div>
                    <div>
                        <h2 className="text-2xl font-bold text-gray-900">
                            Subir archivos
                        </h2>
                        <p className="text-sm text-gray-600">
                            Arrastra o selecciona tus documentos
                        </p>
                    </div>
                </div>
            </div>

            {/* Drag & Drop Zone */}
            <motion.div
                onDragOver={(e) => {
                    e.preventDefault();
                    setIsDragging(true);
                }}
                onDragLeave={() => setIsDragging(false)}
                onDrop={handleDrop}
                animate={{
                    scale: isDragging ? 1.02 : 1,
                }}
                className={`relative overflow-hidden rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-300 md:p-10 ${
                    isDragging
                        ? 'border-gray-400 bg-linear-to-br from-gray-50 to-slate-50 shadow-xl shadow-gray-500/20'
                        : 'border-gray-300 bg-linear-to-br from-gray-50 to-gray-100 hover:border-gray-300 hover:shadow-lg'
                }`}
            >
                {/* Efecto de fondo animado */}
                {isDragging && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        className="absolute inset-0 bg-linear-to-br from-gray-500/5 to-slate-500/5"
                    />
                )}

                <motion.div
                    animate={{ y: isDragging ? -5 : 0 }}
                    className="relative z-10 flex flex-col items-center"
                >
                    <motion.div
                        animate={{
                            y: isDragging ? [0, -10, 0] : 0,
                            rotate: isDragging ? [0, 5, -5, 0] : 0,
                        }}
                        transition={{
                            duration: 0.5,
                            repeat: isDragging ? Infinity : 0,
                        }}
                        className={`mb-4 flex h-16 w-16 items-center justify-center rounded-2xl transition-all duration-300 ${
                            isDragging
                                ? 'bg-linear-to-br from-gray-500 to-gray-600 shadow-2xl shadow-gray-500/40'
                                : 'bg-linear-to-br from-gray-200 to-gray-300'
                        }`}
                    >
                        <Upload
                            className={`h-8 w-8 ${isDragging ? 'text-white' : 'text-gray-500'}`}
                        />
                    </motion.div>

                    <h3
                        className={`mb-2 text-xl font-bold transition-colors ${
                            isDragging ? 'text-gray-600' : 'text-gray-700'
                        }`}
                    >
                        {isDragging
                            ? '¡Suelta tus archivos aquí!'
                            : 'Arrastra tus archivos'}
                    </h3>

                    <p className="mb-4 text-sm text-gray-500">
                        o haz clic para seleccionar
                    </p>

                    <label className="group cursor-pointer">
                        <input
                            type="file"
                            multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            onChange={handleFileInput}
                            className="hidden"
                        />
                        <motion.span
                            whileHover={{ scale: 1.05 }}
                            whileTap={{ scale: 0.95 }}
                            className="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-mbe-accent to-mbe-accent-2 px-6 py-3 font-bold text-white shadow-lg shadow-gray-500/30 transition-all hover:from-mbe-accent-2 hover:to-mbe-accent hover:shadow-xl hover:shadow-gray-500/40"
                        >
                            <File className="h-4 w-4" />
                            Seleccionar Archivos
                        </motion.span>
                    </label>

                    <div className="mt-4 flex flex-wrap items-center justify-center gap-2 text-sm">
                        <div className="flex items-center gap-1.5 rounded-full border border-gray-200 bg-white/80 px-3 py-1.5 backdrop-blur-sm">
                            <FileText className="h-3.5 w-3.5 text-gray-600" />
                            <span className="text-xs font-medium text-gray-700">
                                PDF, Word
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full border border-gray-200 bg-white/80 px-3 py-1.5 backdrop-blur-sm">
                            <Image className="h-3.5 w-3.5 text-gray-600" />
                            <span className="text-xs font-medium text-gray-700">
                                Imágenes
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full border border-gray-200 bg-white/80 px-3 py-1.5 backdrop-blur-sm">
                            <Sparkles className="h-3.5 w-3.5 text-gray-600" />
                            <span className="text-xs font-medium text-gray-700">
                                Hasta {config.limits.max_file_size_mb}MB
                            </span>
                        </div>
                    </div>
                </motion.div>
            </motion.div>

            {/* Error Messages */}
            <AnimatePresence>
                {errors.length > 0 && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="mt-4"
                    >
                        {errors.map((error, idx) => (
                            <motion.div
                                key={idx}
                                initial={{ scale: 0.9 }}
                                animate={{ scale: 1 }}
                                className="flex items-center gap-2 rounded-xl border-2 border-gray-200 bg-linear-to-br from-gray-50 to-slate-50 p-3 text-gray-700 shadow-md"
                            >
                                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-500">
                                    <AlertCircle className="h-4 w-4 text-white" />
                                </div>
                                <span className="text-sm font-medium">
                                    {error}
                                </span>
                            </motion.div>
                        ))}
                    </motion.div>
                )}
            </AnimatePresence>

            {/* Files List */}
            <AnimatePresence>
                {files.length > 0 && (
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: 20 }}
                        className="mt-6"
                    >
                        {/* Header de lista */}
                        <div className="mb-4 flex items-center justify-between rounded-xl border-2 border-gray-100 bg-linear-to-r from-gray-50 to-slate-50 p-3">
                            <div className="flex items-center gap-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-mbe-secondary-2 to-mbe-accent-2 shadow-md">
                                    <CheckCircle2 className="h-4 w-4 text-white" />
                                </div>
                                <div>
                                    <h3 className="text-sm font-bold text-black-900">
                                        Archivos seleccionados
                                    </h3>
                                    <p className="text-xs text-black-600">
                                        {files.length} de {limits.maxFiles}{' '}
                                        archivos
                                    </p>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-xs text-black-600">
                                    Tamaño total
                                </p>
                                <p className="text-sm font-bold text-black-600">
                                    {formatFileSize(totalSize)}
                                </p>
                            </div>
                        </div>

                        {/* Lista de archivos */}
                        <div className="space-y-2">
                            {files.map((file, index) => (
                                <motion.div
                                    key={file.id}
                                    initial={{ opacity: 0, x: -20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    exit={{ opacity: 0, x: 20 }}
                                    transition={{ delay: index * 0.05 }}
                                    className={`group flex items-center gap-3 rounded-xl border-2 p-3 transition-all duration-300 ${
                                        file.errors.length > 0
                                            ? 'border-gray-200 bg-linear-to-br from-gray-50 to-slate-50 shadow-md'
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-lg hover:shadow-gray-500/10'
                                    }`}
                                >
                                    {/* File Icon or Preview */}
                                    <div className="shrink-0">
                                        {file.preview ? (
                                            <div className="relative">
                                                <img
                                                    src={file.preview}
                                                    alt={file.name}
                                                    className="h-12 w-12 rounded-lg object-cover shadow-md"
                                                />
                                                <div className="absolute inset-0 rounded-lg bg-linear-to-br from-gray-500/0 to-gray-500/10"></div>
                                            </div>
                                        ) : (
                                            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-linear-to-br from-gray-100 to-slate-100 text-black-800 shadow-md transition-transform group-hover:scale-110">
                                                {getFileIcon(file.type)}
                                            </div>
                                        )}
                                    </div>

                                    {/* File Info */}
                                    <div className="min-w-0 flex-grow">
                                        <p className="truncate text-sm font-bold text-gray-900">
                                            {file.name}
                                        </p>
                                        <div className="mt-0.5 flex items-center gap-2">
                                            <span className="text-xs font-medium text-gray-600">
                                                {formatFileSize(file.size)}
                                            </span>
                                            {file.errors.length === 0 && (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                                    <CheckCircle2 className="h-3 w-3" />
                                                    Listo
                                                </span>
                                            )}
                                        </div>
                                        {file.errors.length > 0 && (
                                            <div className="mt-1 flex items-center gap-1.5">
                                                <AlertCircle className="h-3.5 w-3.5 text-gray-800" />
                                                <span className="text-xs font-medium text-gray-600">
                                                    {file.errors[0]}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Remove Button */}
                                    <div className="shrink-0">
                                        <motion.button
                                            whileHover={{
                                                scale: 1.1,
                                                rotate: 90,
                                            }}
                                            whileTap={{ scale: 0.9 }}
                                            onClick={() => removeFile(file.id)}
                                            className="group/button rounded-lg p-2 transition-all duration-200 hover:bg-gray-100"
                                        >
                                            <X className="h-4 w-4 text-gray-400 transition-colors group-hover/button:text-black-700" />
                                        </motion.button>
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* Action Buttons */}
            <div className="mt-6 flex justify-end border-t-2 border-gray-100 pt-6">
                <motion.button
                    whileHover={{
                        scale: canContinue ? 1.05 : 1,
                        x: canContinue ? 5 : 0,
                    }}
                    whileTap={{ scale: canContinue ? 0.95 : 1 }}
                    disabled={!canContinue}
                    onClick={() => canContinue && onNext()}
                    className={`flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-bold transition-all ${
                        canContinue
                            ? 'bg-linear-to-r from-mbe-accent to-mbe-accent-2 text-white shadow-xl shadow-gray-500/30 hover:from-mbe-accent-2 hover:to-mbe-accent hover:shadow-2xl hover:shadow-gray-500/40'
                            : 'cursor-not-allowed bg-gray-200 text-gray-400'
                    }`}
                >
                    Continuar
                    <motion.div
                        animate={{ x: canContinue ? [0, 5, 0] : 0 }}
                        transition={{ duration: 1, repeat: Infinity }}
                    >
                        <ChevronRight className="h-5 w-5" />
                    </motion.div>
                </motion.button>
            </div>

            {/* Info adicional con icono */}
            {files.length === 0 && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 0.5 }}
                    className="mt-6 flex items-center justify-center gap-2 text-sm"
                >
                    <div className="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-blue-700">
                        <Lightbulb className="h-4 w-4" />
                        <span className="font-medium">
                            Puedes subir hasta {limits.maxFiles} archivos a la
                            vez
                        </span>
                    </div>
                </motion.div>
            )}
        </div>
    );
};

export default Step1UploadFiles;
