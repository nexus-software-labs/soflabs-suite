<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Core\Promotion;
use App\Models\Core\PromotionApplication;
use App\Models\Printing\PrintOrder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Parser as PdfParser;

class PrintOrderService
{
    protected PromotionService $promotionService;

    protected CustomerService $customerService;

    public function __construct(
        PromotionService $promotionService,
        CustomerService $customerService
    ) {
        $this->promotionService = $promotionService;
        $this->customerService = $customerService;
    }

    /**
     * Crear un pedido de impresión con soporte para promociones
     */
    public function createPrintOrder($data, $request, $promotion = null)
    {
        // Analizar archivos para obtener páginas reales
        $filesAnalysis = $this->analyzeFiles($request->file('files'));
        $totalPages = $filesAnalysis['total_pages'];

        $calculation = $this->calculateOrderTotal([
            'total_pages' => $totalPages,
            'config' => [
                'service_type' => 'printing',
                'print_type' => $data['config']['printType'],
                'paper_size' => $data['config']['paperSize'],
                'paper_type' => $data['config']['paperType'] ?? 'bond',
                'copies' => $data['config']['copies'],
                'double_sided' => $data['config']['doubleSided'] ?? false,
                'binding' => $data['config']['binding'] ?? false,
            ],
            'delivery' => [
                'method' => $data['delivery']['method'],
                'is_national' => false,
                'distance' => 0,
            ],
        ]);

        // 🎯 APLICAR DESCUENTO DE PROMOCIÓN SI EXISTE
        $discountAmount = 0;
        $deliveryDiscountAmount = 0;

        if ($promotion) {
            if ($promotion->applies_to === 'subtotal') {
                // Descuento en el subtotal
                $discountAmount = $this->promotionService->calculateDiscount(
                    $promotion,
                    $calculation['breakdown']['subtotal'],
                    0
                );
            } elseif ($promotion->applies_to === 'delivery') {
                // Descuento en el delivery
                $deliveryDiscountAmount = $this->promotionService->calculateDiscount(
                    $promotion,
                    0,
                    $calculation['breakdown']['delivery']
                );
            }
        }

        // Recalcular total con descuentos
        $finalSubtotal = $calculation['breakdown']['subtotal'] - $discountAmount;
        $finalDeliveryCost = $calculation['breakdown']['delivery'] - $deliveryDiscountAmount;
        $finalTotal = $finalSubtotal + $finalDeliveryCost + $calculation['breakdown']['tax'];

        // Crear el pedido
        $order = PrintOrder::create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => auth('sanctum')->id() ?: auth()->id(),
            'customer_name' => $data['customer']['name'],
            'customer_email' => $data['customer']['email'],
            'customer_phone' => $data['customer']['phone'] ?? null,

            // Estado
            'status' => 'pending',
            'payment_status' => 'pending',

            // Configuración de impresión
            'print_type' => $data['config']['printType'],
            'paper_size' => $data['config']['paperSize'],
            'is_plan' => ($data['config']['paperSize'] === 'double_letter'),
            'paper_type' => $data['config']['paperType'] ?? 'bond',
            'orientation' => $data['config']['orientation'],
            'copies' => $data['config']['copies'],
            'binding' => $data['config']['binding'] ?? false,
            'double_sided' => $data['config']['doubleSided'] ?? false,
            'page_range' => $data['config']['pageRange'] ?? 'all',
            'pages_count' => $totalPages,

            // Entrega (sucursal para pickup)
            'delivery_method' => $data['delivery']['method'],
            'branch_id' => $data['delivery']['branch_id'] ?? null,
            'customer_address_id' => $data['delivery']['customerAddressId'] ?? null,
            'delivery_address' => $data['delivery']['address'] ?? null,
            'delivery_phone' => $data['delivery']['phone'] ?? null,
            'delivery_notes' => $data['delivery']['notes'] ?? null,
            'delivery_cost' => $finalDeliveryCost,

            'price_per_page' => $calculation['breakdown']['unit_price'],
            'binding_price' => $calculation['breakdown']['binding'],
            'double_sided_cost' => $calculation['breakdown']['double_sided'],
            'subtotal' => $finalSubtotal,
            'tax' => $calculation['breakdown']['tax'],
            'total' => $finalTotal, // 🎯 Total final con descuentos

            'promotion_id' => $promotion?->id,
            'discount' => $discountAmount,
            'delivery_discount' => $deliveryDiscountAmount,

            'customer_notes' => $data['customer']['notes'] ?? null,
        ]);

        // Guardar archivos con Spatie Media Library
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $order->addMedia($file)
                    ->toMediaCollection('print-files');
            }
        }

        // Registrar en el historial (sanctum para API, web para navegador)
        $order->history()->create([
            'status' => 'pending',
            'comment' => 'Pedido recibido',
            'created_by' => auth('sanctum')->id() ?: auth()->id(),
        ]);

        // 🎯 REGISTRAR APLICACIÓN DE PROMOCIÓN
        if ($promotion) {
            PromotionApplication::create([
                'promotion_id' => $promotion->id,
                'applicable_type' => PrintOrder::class,
                'applicable_id' => $order->id,
                'original_amount' => $promotion->applies_to === 'delivery'
                    ? $calculation['breakdown']['delivery']
                    : $calculation['breakdown']['subtotal'],
                'discount_amount' => $discountAmount + $deliveryDiscountAmount,
                'applied_to' => $promotion->applies_to,
                'applied_at' => now(),
            ]);
        }

        return $order;
    }

    public function formatOrderData($order)
    {
        return [
            'success' => true,
            'order' => [
                'orderNumber' => $order->order_number,
                'status' => $order->status,
                'createdAt' => $order->created_at->toISOString(),
                'customerName' => $order->customer_name,
                'customerEmail' => $order->customer_email,
                'customerPhone' => $order->customer_phone,
                'total' => (float) $order->total,
                'pagesCount' => $order->pages_count,
                'files' => $order->getMedia('print-files')->map(fn ($media) => [
                    'name' => $media->file_name,
                    'pages' => 10,
                ]),
                'config' => [
                    'printType' => $order->print_type,
                    'paperSize' => $order->paper_size,
                    'copies' => $order->copies,
                    'binding' => $order->binding,
                ],
                'delivery' => [
                    'method' => $order->delivery_method,
                    'location' => $order->pickupLocation
                        ? $order->pickupLocation->name.' - '.$order->pickupLocation->address
                        : $order->delivery_address,
                ],
                'history' => $order->history->map(fn ($h) => [
                    'status' => $h->status,
                    'timestamp' => $h->created_at->toISOString(),
                    'comment' => $h->comment,
                ]),
            ],
        ];
    }

    /**
     * Calcular precio unitario según tipo, color, tamaño y cantidad
     */
    public function calculateUnitPrice(string $type, string $color, string $size, int $quantity): float
    {
        $priceRanges = config("printing.prices.{$type}.{$color}.{$size}", []);

        foreach ($priceRanges as $range) {
            $min = $range['min'];
            $max = $range['max'];

            // Si la cantidad está dentro del rango
            if ($quantity >= $min && ($max === null || $quantity <= $max)) {
                return (float) $range['price'];
            }
        }

        // Si no encuentra rango, devolver 0
        return 0.0;
    }

    /**
     * Obtener precio de engargolado según número de hojas
     */
    public function getBindingPrice(int $sheets): float
    {
        $bindingOptions = config('printing.prices.binding', []);

        foreach ($bindingOptions as $option) {
            if ($sheets <= $option['max_sheets']) {
                return (float) $option['price'];
            }
        }

        // Si supera todas las opciones, devolver la última
        $lastOption = end($bindingOptions);

        return $lastOption ? (float) $lastOption['price'] : 0.0;
    }

    /**
     * Calcular precio de corte de papel según cantidad
     */
    public function getPaperCuttingPrice(int $quantity): float
    {
        $cuttingRanges = config('printing.services.paper_cutting', []);

        foreach ($cuttingRanges as $range) {
            $min = $range['min'];
            $max = $range['max'];

            if ($quantity >= $min && ($max === null || $quantity <= $max)) {
                return (float) $range['price'];
            }
        }

        return 0.0;
    }

    /**
     * Calcular el costo total de una orden de impresión
     *
     * @param  array  $orderData  Datos de la orden
     * @return array Desglose completo de costos
     */
    public function calculateOrderTotal(array $orderData): array
    {
        $config = $orderData['config'];
        $delivery = $orderData['delivery'] ?? [];

        // Determinar si es printing o copies
        $serviceType = $config['service_type'] ?? 'printing'; // 'printing' o 'copies'
        $printType = $config['print_type']; // 'bw' o 'color'
        $paperSize = $config['paper_size']; // 'letter', 'legal', 'double_letter'
        $copies = (int) $config['copies'];
        $totalPages = (int) $orderData['total_pages'];

        // Calcular cantidad total (páginas * copias)
        $totalQuantity = $totalPages * $copies;

        // 1. Costo base de impresión/copias
        $unitPrice = $this->calculateUnitPrice($serviceType, $printType, $paperSize, $totalQuantity);
        $basePrice = $unitPrice * $totalQuantity;

        // 2. Costo de tipo de papel
        $paperTypePrice = 0;
        if (isset($config['paper_type']) && $config['paper_type'] !== 'bond') {
            $paperTypePrice = (float) config("printing.prices.paper_type.{$config['paper_type']}", 0);
            $paperTypePrice *= $totalQuantity;
        }

        // 3. Costo de impresión doble cara
        $doubleSidedPrice = 0;
        if (! empty($config['double_sided']) && $config['double_sided'] == true) {
            $doubleSidedPrice = (float) config('printing.prices.double_sided', 0) * $totalPages;
        }

        // 4. Costo de engargolado
        $bindingPrice = 0;
        if (! empty($config['binding']) && $config['binding'] == true) {
            $bindingPrice = $this->getBindingPrice($totalPages);
        }

        // 5. Costo de tarjetas de presentación (si aplica)
        $businessCardsPrice = 0;
        if (isset($config['business_cards']) && (int) $config['business_cards'] > 0) {
            $businessCardsUnitPrice = (float) config('printing.prices.business_cards', 0);
            $businessCardsPrice = $businessCardsUnitPrice * (int) $config['business_cards'];
        }

        // 6. Costo de corte de papel (si aplica)
        $paperCuttingPrice = 0;
        if (! empty($config['paper_cutting']) && $config['paper_cutting'] == true) {
            $paperCuttingPrice = $this->getPaperCuttingPrice($totalQuantity);
        }

        // Subtotal
        $subtotal = $basePrice + $paperTypePrice + $doubleSidedPrice + $bindingPrice + $businessCardsPrice + $paperCuttingPrice;

        // 7. Costo de entrega
        $deliveryPrice = 0;
        if (isset($delivery['method']) && $delivery['method'] === 'delivery') {
            // Verificar si califica para envío gratis
            $freeDeliveryMinimum = (float) config('printing.delivery.free_delivery_minimum', 20);

            if ($subtotal < $freeDeliveryMinimum) {
                if (isset($delivery['is_national']) && $delivery['is_national'] == true) {
                    // Envío nacional
                    $deliveryPrice = (float) config('printing.delivery.national', 0);
                } else {
                    // Envío local (base + por km)
                    $baseCost = (float) config('printing.delivery.base_cost', 0);
                    $perKm = (float) config('printing.delivery.per_km', 0);
                    $distance = (float) ($delivery['distance'] ?? 0);

                    $deliveryPrice = $baseCost + ($perKm * $distance);
                }
            }
        }

        $subtotalWithDelivery = $subtotal + $deliveryPrice;

        // 8. IVA (13%)
        $taxRate = (float) config('printing.tax.iva', 0.13);
        $tax = $subtotalWithDelivery * $taxRate;

        // Total final
        $total = $subtotalWithDelivery + $tax;

        return [
            'breakdown' => [
                'base_price' => round($basePrice, 2),
                'unit_price' => round($unitPrice, 2),
                'quantity' => $totalQuantity,
                'paper_type' => round($paperTypePrice, 2),
                'double_sided' => round($doubleSidedPrice, 2),
                'binding' => round($bindingPrice, 2),
                'business_cards' => round($businessCardsPrice, 2),
                'paper_cutting' => round($paperCuttingPrice, 2),
                'subtotal' => round($subtotal, 2),
                'delivery' => round($deliveryPrice, 2),
                'subtotal_with_delivery' => round($subtotalWithDelivery, 2),
                'tax' => round($tax, 2),
                'tax_rate' => $taxRate,
            ],
            'total' => round($total, 2),
            'items_summary' => [
                'total_pages' => $totalPages,
                'copies' => $copies,
                'total_quantity' => $totalQuantity,
                'service_type' => $serviceType,
                'print_type' => $printType,
                'paper_size' => $paperSize,
            ],
        ];
    }

    /**
     * Analizar múltiples archivos y obtener información
     */
    public function analyzeFiles(array $files): array
    {
        $results = [];
        $totalPages = 0;

        foreach ($files as $file) {
            try {
                $pageCount = $this->getPageCount($file, $file->getMimeType());
                $totalPages += $pageCount;

                // Obtener dimensiones si es PDF
                $dimensions = null;
                $isBlueprint = false;
                $blueprintInfo = null;

                if ($file->getMimeType() === 'application/pdf') {
                    $dimensions = $this->getPdfDimensions($file);

                    if ($dimensions && ! isset($dimensions['error'])) {
                        $isBlueprint = $this->isBlueprint($dimensions);

                        // if ($isBlueprint) {
                        //     $totalBlueprints++;
                        //     $blueprintInfo = $this->calculateBlueprintPrice(
                        //         $dimensions,
                        //         1,
                        //         'bond'
                        //     );
                        // }
                    }
                }

                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'pages' => $pageCount,
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatFileSize($file->getSize()),
                    'mime_type' => $file->getMimeType(),
                    'dimensions' => $dimensions,
                    'is_blueprint' => $isBlueprint,
                    'blueprint_info' => $blueprintInfo,
                    'success' => true,
                ];
            } catch (\Exception $e) {
                $estimatedPages = $this->estimatePageCountByFileSize($file);
                $totalPages += $estimatedPages;

                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'pages' => $estimatedPages,
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatFileSize($file->getSize()),
                    'mime_type' => $file->getMimeType(),
                    'dimensions' => null,
                    'success' => false,
                    'error' => 'No se pudo analizar, estimación aproximada',
                ];
            }
        }

        return [
            'files' => $results,
            'total_pages' => $totalPages,
            'total_files' => count($files),
        ];
    }

    /**
     * Obtener número de páginas según tipo de archivo
     */
    private function getPageCount(UploadedFile $file, string $mimeType): int
    {
        switch ($mimeType) {
            case 'application/pdf':
                return $this->getPdfPageCount($file);

            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return $this->getWordPageCount($file);

            case 'image/jpeg':
            case 'image/png':
            case 'image/jpg':
                return 1; // Imágenes = 1 página

            default:
                throw new \Exception('Tipo de archivo no soportado');
        }
    }

    /**
     * Obtener dimensiones de un PDF en pulgadas
     */
    public function getPdfDimensions($file): array
    {
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($file->getPathname());

            $pages = $pdf->getPages();

            if (empty($pages)) {
                return [
                    'width_inches' => null,
                    'height_inches' => null,
                    'error' => 'No se pudieron obtener las páginas',
                ];
            }

            // Obtener dimensiones de la primera página
            $firstPage = $pages[0];
            $details = $firstPage->getDetails();

            // Las dimensiones pueden estar en diferentes lugares
            $mediaBox = $details['MediaBox'] ?? null;

            if ($mediaBox) {
                // MediaBox formato: [x1, y1, x2, y2]
                $width = $mediaBox[2] - $mediaBox[0];  // en puntos
                $height = $mediaBox[3] - $mediaBox[1]; // en puntos

                // Convertir de puntos a pulgadas (72 puntos = 1 pulgada)
                $widthInches = round($width / 72, 2);
                $heightInches = round($height / 72, 2);

                return [
                    'width_inches' => $widthInches,
                    'height_inches' => $heightInches,
                    'width_points' => $width,
                    'height_points' => $height,
                    'page_size' => $this->detectPageSize($widthInches, $heightInches),
                ];
            }

            return [
                'width_inches' => null,
                'height_inches' => null,
                'error' => 'No se encontró MediaBox',
            ];

        } catch (\Exception $e) {
            return [
                'width_inches' => null,
                'height_inches' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Detectar el tamaño de página común
     */
    private function detectPageSize(float $width, float $height): string
    {
        $sizes = [
            'Letter' => [8.5, 11],
            'Legal' => [8.5, 14],
            'A4' => [8.27, 11.69],
            'A3' => [11.69, 16.54],
            'Tabloid' => [11, 17],
        ];

        $tolerance = 0.1; // tolerancia de 0.1 pulgadas

        foreach ($sizes as $name => $dimensions) {
            if (
                abs($width - $dimensions[0]) < $tolerance &&
                abs($height - $dimensions[1]) < $tolerance
            ) {
                return $name;
            }

            // Verificar orientación horizontal
            if (
                abs($width - $dimensions[1]) < $tolerance &&
                abs($height - $dimensions[0]) < $tolerance
            ) {
                return $name.' (Horizontal)';
            }
        }

        return 'Personalizado';
    }

    /**
     * Contar páginas de PDF
     */
    private function getPdfPageCount(UploadedFile $file): int
    {
        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($file->getRealPath());
            $pages = $pdf->getPages();

            return count($pages);
        } catch (\Exception $e) {
            return $this->getPdfPageCountAlternative($file);
        }
    }

    /**
     * Método alternativo para contar páginas de PDF
     */
    private function getPdfPageCountAlternative(UploadedFile $file): int
    {
        $content = file_get_contents($file->getRealPath());

        // Método 1: Contar objetos /Page
        if (preg_match_all("/\/Type\s*\/Page[^s]/i", $content, $matches)) {
            return count($matches[0]);
        }

        // Método 2: Buscar /Count
        if (preg_match("/\/Count\s+(\d+)/", $content, $matches)) {
            return (int) $matches[1];
        }

        // Fallback: estimar por tamaño
        return $this->estimatePageCountByFileSize($file);
    }

    /**
     * Contar páginas de Word
     */
    private function getWordPageCount(UploadedFile $file): int
    {
        try {
            if ($file->getClientOriginalExtension() === 'docx') {
                $phpWord = WordIOFactory::load($file->getRealPath());
                $sections = $phpWord->getSections();

                $elementCount = 0;
                foreach ($sections as $section) {
                    $elementCount += count($section->getElements());
                }

                // Aproximación: ~40 elementos por página
                return max(1, (int) ceil($elementCount / 40));
            }

            return $this->estimatePageCountByFileSize($file);
        } catch (\Exception $e) {
            return $this->estimatePageCountByFileSize($file);
        }
    }

    /**
     * Estimar páginas por tamaño de archivo
     */
    private function estimatePageCountByFileSize(UploadedFile $file): int
    {
        // ~50KB por página (estimación conservadora)
        $sizeInKb = $file->getSize() / 1024;

        return max(1, (int) ceil($sizeInKb / 50));
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2).' '.$sizes[$i];
    }

    /**
     * Validar configuración de orden
     */
    public function validateOrderConfig(array $config): array
    {
        $errors = [];

        // Validar tipo de servicio
        if (! in_array($config['service_type'] ?? '', ['printing', 'copies'])) {
            $errors[] = 'Tipo de servicio inválido';
        }

        // Validar tipo de impresión
        if (! in_array($config['print_type'] ?? '', ['bw', 'color'])) {
            $errors[] = 'Tipo de impresión inválido';
        }

        // Validar tamaño de papel
        $validSizes = array_keys(config('printing.paper_sizes', []));
        if (! in_array($config['paper_size'] ?? '', $validSizes)) {
            $errors[] = 'Tamaño de papel inválido';
        }

        // Validar copias
        $maxCopies = config('printing.limits.max_copies', 100);
        if (! isset($config['copies']) || $config['copies'] < 1 || $config['copies'] > $maxCopies) {
            $errors[] = "Número de copias debe estar entre 1 y {$maxCopies}";
        }

        return $errors;
    }

    /**
     * Obtener configuración completa de precios formateada para API
     */
    public function getPricesConfiguration(): array
    {
        return [
            'prices' => [
                'printing' => config('printing.prices.printing'),
                'copies' => config('printing.prices.copies'),
                'paper_type' => config('printing.prices.paper_type'),
                'binding' => config('printing.prices.binding'),
                'double_sided' => config('printing.prices.double_sided'),
                'business_cards' => config('printing.prices.business_cards'),
            ],
            'services' => config('printing.services'),
            'delivery' => config('printing.delivery'),
            'limits' => config('printing.limits'),
            'paper_sizes' => config('printing.paper_sizes'),
            'allowed_file_types' => config('printing.allowed_file_types'),
        ];
    }

    /**
     * Detectar si un PDF es un plano (blueprint) basándose en sus dimensiones
     */
    private function isBlueprint(array $dimensions): bool
    {
        if (! isset($dimensions['width_inches']) || ! isset($dimensions['height_inches'])) {
            return false;
        }

        $minWidth = config('printing.blueprints.min_dimensions.width', 11);
        $minHeight = config('printing.blueprints.min_dimensions.height', 17);

        // Si el ancho O el alto superan los límites, es un plano
        return $dimensions['width_inches'] > $minWidth ||
                $dimensions['height_inches'] > $minHeight;
    }

    /**
     * Calcular pulgadas lineales de un plano
     * Las pulgadas lineales generalmente se calculan sumando ancho + alto
     * O solo el lado más largo, dependiendo de tu negocio
     */
    private function calculateLinearInches(array $dimensions): float
    {
        if (! isset($dimensions['width_inches']) || ! isset($dimensions['height_inches'])) {
            return 0;
        }

        $linearInches = $dimensions['width_inches'] + $dimensions['height_inches'];

        // $linearInches = max($dimensions['width_inches'], $dimensions['height_inches']);

        return round($linearInches, 2);
    }

    /**
     * Calcular precio de un plano
     */
    private function calculateBlueprintPrice(array $dimensions, int $copies = 1, string $type = 'bond'): array
    {
        $linearInches = $this->calculateLinearInches($dimensions);
        $pricePerInch = config("printing.blueprints.plotting.{$type}", 0.20);

        $pricePerCopy = $linearInches * $pricePerInch;
        $totalPrice = $pricePerCopy * $copies;

        return [
            'linear_inches' => $linearInches,
            'price_per_inch' => $pricePerInch,
            'price_per_copy' => round($pricePerCopy, 2),
            'total_price' => round($totalPrice, 2),
            'copies' => $copies,
        ];
    }

    /**
     * Generar número de orden único
     */
    private function generateOrderNumber()
    {
        return 'IMP-'.strtoupper(uniqid());
    }

    /**
     * Obtener datos del formulario para crear orden
     */
    public function getFormData(): array
    {
        $customer = $this->customerService->getCurrent();
        $formData = $this->customerService->getFormData($customer);

        $suggestedBranch = $this->customerService->getSuggestedBranch($customer);

        // Obtener promociones activas
        $deliveryPromotion = null;
        $subtotalPromotion = null;

        if ($suggestedBranch) {
            // Buscar promoción de delivery
            $deliveryPromotions = $this->promotionService->getActivePromotionsForBranch(
                $suggestedBranch->id,
                'print_order'
            )->where('applies_to', 'delivery');

            if ($deliveryPromotions->isNotEmpty()) {
                $promotion = $deliveryPromotions->first();
                $deliveryPromotion = [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'description' => $promotion->description,
                    'discount_type' => $promotion->discount_type,
                    'discount_value' => $promotion->discount_value,
                    'applies_to' => $promotion->applies_to,
                    'label' => $promotion->getDiscountLabel(),
                    'min_order_amount' => $promotion->min_order_amount,
                ];
            }

            // Buscar promoción de subtotal
            $subtotalPromotions = $this->promotionService->getActivePromotionsForBranch(
                $suggestedBranch->id,
                'print_order'
            )->where('applies_to', 'subtotal');

            if ($subtotalPromotions->isNotEmpty()) {
                $promotion = $subtotalPromotions->first();
                $subtotalPromotion = [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'description' => $promotion->description,
                    'discount_type' => $promotion->discount_type,
                    'discount_value' => $promotion->discount_value,
                    'applies_to' => $promotion->applies_to,
                    'label' => $promotion->getDiscountLabel(),
                    'discount_label' => $promotion->getDiscountLabel(),
                    'min_order_amount' => $promotion->min_order_amount,
                ];
            }
        }

        return [
            'branches' => $formData['branches'],
            'customerAddresses' => $formData['customerAddresses'],
            'suggestedBranchId' => $formData['suggestedBranchId'],
            'deliveryPromotion' => $deliveryPromotion,
            'subtotalPromotion' => $subtotalPromotion,
            'config' => config('printing'),
            'isVerified' => $formData['isVerified'],
            'showVerificationModal' => ! $formData['isVerified'],
        ];
    }

    /**
     * Obtener órdenes del usuario con filtros
     */
    public function getOrdersByUser(int $userId, int $perPage = 10, array $filters = [])
    {
        $query = PrintOrder::where('user_id', $userId)
            ->with(['pickupLocation', 'branch', 'history', 'promotion'])
            ->latest();

        // Filtrar por estado del pedido (status) o por estado de pago (payment_status)
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'paid') {
                $query->where('payment_status', 'paid');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Filtrar por rango de fechas
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Obtener estadísticas de órdenes del usuario
     */
    public function getOrderStats(int $userId): array
    {
        return [
            'total' => PrintOrder::where('user_id', $userId)->count(),
            'pending' => PrintOrder::where('user_id', $userId)->where('status', 'pending')->count(),
            'in_queue' => PrintOrder::where('user_id', $userId)->where('status', 'in_queue')->count(),
            'printing' => PrintOrder::where('user_id', $userId)->where('status', 'printing')->count(),
            'ready' => PrintOrder::where('user_id', $userId)->where('status', 'ready')->count(),
            'delivered' => PrintOrder::where('user_id', $userId)->where('status', 'delivered')->count(),
            'total_spent' => PrintOrder::where('user_id', $userId)
                ->where('payment_status', 'paid')
                ->sum('total'),
        ];
    }

    /**
     * Validar y obtener promoción
     */
    public function validateAndGetPromotion(Request $request, string $branchId, string $promotionField = 'promotion_id', string $couponField = 'coupon_code'): ?Promotion
    {
        if (! $request->filled($promotionField)) {
            return null;
        }

        $promotion = Promotion::find($request->input($promotionField));

        if (! $promotion) {
            return null;
        }

        if ($request->filled($couponField)) {
            $result = $this->promotionService->validateCouponCode(
                (string) $request->input($couponField),
                $branchId,
                'print_order',
                0,
                0
            );

            $resolved = ($result['valid'] ?? false) ? ($result['promotion'] ?? null) : null;

            if (! $resolved || $resolved->id !== $promotion->id) {
                throw new \Exception('Cupón inválido o expirado');
            }
        }

        return $promotion;
    }

    /**
     * ULID de sucursal (o primera sucursal activa del tenant).
     */
    public function getBranchId(?string $branchId): string
    {
        if ($branchId && Branch::query()->where('id', $branchId)->where('is_active', true)->exists()) {
            return $branchId;
        }

        $defaultBranch = Branch::query()->where('is_active', true)->orderByDesc('is_main')->first();

        if (! $defaultBranch) {
            throw new \Exception('No hay sucursales activas disponibles');
        }

        return $defaultBranch->id;
    }

    /**
     * Crear orden de impresión (versión mejorada)
     */
    public function createOrder(array $validated, Request $request): PrintOrder
    {
        return DB::transaction(function () use ($validated, $request) {
            $incomingBranch = $validated['delivery']['branch_id'] ?? null;
            $branchId = $this->getBranchId($incomingBranch !== null ? (string) $incomingBranch : null);
            $validated['delivery']['branch_id'] = $branchId;

            $promotion = null;
            if (isset($validated['promotion_id'])) {
                $promotion = $this->validateAndGetPromotion($request, $branchId, 'promotion_id', 'coupon_code');
            }

            // Crear la orden usando el método existente
            $order = $this->createPrintOrder($validated, $request, $promotion);

            return $order;
        });
    }
}
