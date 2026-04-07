<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Precios de Impresión por Volumen
    |--------------------------------------------------------------------------
    */

    'prices' => [
        'printing' => [
            'bw' => [
                'letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.10],
                    ['min' => 100, 'max' => 499, 'price' => 0.09],
                    ['min' => 500, 'max' => 999, 'price' => 0.08],
                    ['min' => 1000, 'max' => null, 'price' => 0.07],
                ],
                'legal' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.12],
                    ['min' => 100, 'max' => 499, 'price' => 0.11],
                    ['min' => 500, 'max' => 999, 'price' => 0.10],
                    ['min' => 1000, 'max' => null, 'price' => 0.09],
                ],
                'double_letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.13],
                    ['min' => 100, 'max' => 499, 'price' => 0.12],
                    ['min' => 500, 'max' => 999, 'price' => 0.11],
                    ['min' => 1000, 'max' => null, 'price' => 0.10],
                ],
            ],
            'color' => [
                'letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.30],
                    ['min' => 100, 'max' => 499, 'price' => 1.25],
                    ['min' => 500, 'max' => 999, 'price' => 1.60],
                    ['min' => 1000, 'max' => null, 'price' => 1.10],
                ],
                'legal' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.40],
                    ['min' => 100, 'max' => 499, 'price' => 1.35],
                    ['min' => 500, 'max' => 999, 'price' => 1.30],
                    ['min' => 1000, 'max' => null, 'price' => 1.25],
                ],
                'double_letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.55],
                    ['min' => 100, 'max' => 499, 'price' => 1.50],
                    ['min' => 500, 'max' => 999, 'price' => 1.40],
                    ['min' => 1000, 'max' => null, 'price' => 1.35],
                ],
            ],
        ],

        // COPIAS - Precios por cantidad
        'copies' => [
            'bw' => [
                'letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.08],
                    ['min' => 100, 'max' => 499, 'price' => 0.07],
                    ['min' => 500, 'max' => 999, 'price' => 0.06],
                    ['min' => 1000, 'max' => 4999, 'price' => 0.06],
                    ['min' => 5000, 'max' => null, 'price' => 0.06],
                ],
                'legal' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.10],
                    ['min' => 100, 'max' => 499, 'price' => 0.09],
                    ['min' => 500, 'max' => 999, 'price' => 0.08],
                    ['min' => 1000, 'max' => 4999, 'price' => 0.07],
                    ['min' => 5000, 'max' => null, 'price' => 0.07],
                ],
                'double_letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 0.12],
                    ['min' => 100, 'max' => 499, 'price' => 0.11],
                    ['min' => 500, 'max' => 999, 'price' => 0.10],
                    ['min' => 1000, 'max' => 4999, 'price' => 0.09],
                    ['min' => 5000, 'max' => null, 'price' => 0.09],
                ],
            ],
            'color' => [
                'letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.25],
                    ['min' => 100, 'max' => 499, 'price' => 1.20],
                    ['min' => 500, 'max' => 999, 'price' => 1.10],
                    ['min' => 1000, 'max' => 4999, 'price' => 1.04],
                    ['min' => 5000, 'max' => null, 'price' => 0.98],
                ],
                'legal' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.35],
                    ['min' => 100, 'max' => 499, 'price' => 1.30],
                    ['min' => 500, 'max' => 999, 'price' => 1.25],
                    ['min' => 1000, 'max' => 4999, 'price' => 1.20],
                    ['min' => 5000, 'max' => null, 'price' => 1.10],
                ],
                'double_letter' => [
                    ['min' => 1, 'max' => 99, 'price' => 1.50],
                    ['min' => 100, 'max' => 499, 'price' => 1.40],
                    ['min' => 500, 'max' => 999, 'price' => 1.35],
                    ['min' => 1000, 'max' => 4999, 'price' => 1.30],
                    ['min' => 5000, 'max' => null, 'price' => 1.30],
                ],
            ],
        ],

        // Tipos de papel
        'paper_type' => [
            'bond' => 0.00,
            'photo_glossy' => 0.10,
        ],

        // Engargolado según cantidad de hojas
        'binding' => [
            ['max_sheets' => 25, 'price' => 2.10],
            ['max_sheets' => 85, 'price' => 2.28],
            ['max_sheets' => 110, 'price' => 2.40],
            ['max_sheets' => 180, 'price' => 2.50],
            ['max_sheets' => 300, 'price' => 2.70],
        ],

        // Impresión doble cara
        'double_sided' => 0.20,

        // Tarjetas de presentación
        'business_cards' => 0.31,
    ],

    /*
    |--------------------------------------------------------------------------
    | Servicios adicionales
    |--------------------------------------------------------------------------
    */

    'services' => [
        // Corte de papel - precios por volumen del Excel
        'paper_cutting' => [
            ['min' => 1, 'max' => 99, 'price' => 0.20],
            ['min' => 100, 'max' => 499, 'price' => 0.15],
            ['min' => 500, 'max' => 999, 'price' => 0.13],
            ['min' => 1000, 'max' => null, 'price' => 0.10],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Servicios de Planos (Blueprints)
    |--------------------------------------------------------------------------
    */

    'blueprints' => [
        'plotting' => [
            'bond' => 0.20,
            'bond_bw' => 0.18,
        ],

        // Escaneo de planos
        'scanning' => 2.00,

        // Si el ancho o alto es mayor a estas pulgadas, es un plano
        'min_dimensions' => [
            'width' => 11,
            'height' => 17,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Costos de Envío
    |--------------------------------------------------------------------------
    */

    'delivery' => [
        'national' => 3.75,
        'base_cost' => 2.00,
        'per_km' => 0.50,
        'free_delivery_minimum' => 20.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Límites y Restricciones
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'max_file_size_mb' => 50,
        'max_pages' => 500,
        'max_copies' => 100,
        'max_files_per_order' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Formatos de Archivo Permitidos
    |--------------------------------------------------------------------------
    */

    'allowed_file_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'image/jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tamaños de Papel
    |--------------------------------------------------------------------------
    */

    'paper_sizes' => [
        'letter' => 'Carta (Letter)',
        'legal' => 'Oficio (Legal)',
        'double_letter' => 'Doble Carta',
        'a3' => 'A3',
        'a2' => 'A2 (Plano)',
        'a1' => 'A1 (Plano)',
        'a0' => 'A0 (Plano)',
        'arch_d' => 'ARCH D (Plano)',
        'arch_e' => 'ARCH E (Plano)',
        'custom_blueprint' => 'Plano Personalizado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados del Pedido
    |--------------------------------------------------------------------------
    */

    'statuses' => [
        'pending' => '📤 Pendiente',
        'processing' => '🖨️ En Proceso',
        'ready' => '✅ Listo',
        'delivered' => '✓ Entregado',
        'cancelled' => '❌ Cancelado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Métodos de Pago
    |--------------------------------------------------------------------------
    */

    'payment_methods' => [
        'cash' => 'Efectivo',
        'card' => 'Tarjeta',
        'transfer' => 'Transferencia',
    ],

    /*
    |--------------------------------------------------------------------------
    | IVA
    |--------------------------------------------------------------------------
    */

    'tax' => [
        'iva' => 0.13, // 13%
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificaciones
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email' => true,
        'sms' => false,
        'whatsapp' => false,
    ],
];
