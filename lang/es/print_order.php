<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Pedidos de impresión',
    'model_label' => 'pedido de impresión',
    'plural_model_label' => 'Pedidos de impresión',

    'tabs' => [
        'all' => 'Todos',
        'pending' => 'Pendientes',
        'delivered' => 'Entregados',
    ],

    'table' => [
        'order_number' => 'N.º pedido',
        'customer_name' => 'Cliente',
        'customer_email' => 'Correo',
        'customer_phone' => 'Teléfono',
        'branch' => 'Sucursal',
        'status' => 'Estado',
        'pages_count' => 'Páginas',
        'pages_suffix' => 'pág.',
        'copies' => 'Copias',
        'print_type' => 'Tipo',
        'delivery_method' => 'Entrega',
        'pickup_location' => 'Punto de recogida',
        'total' => 'Total',
        'payment_status' => 'Pago',
        'created_at' => 'Creado',
        'downloaded_at' => 'Descargado',
    ],

    'filters' => [
        'status' => 'Estado',
        'payment_status' => 'Estado de pago',
        'delivery_method' => 'Método de entrega',
        'print_type' => 'Tipo de impresión',
        'pickup' => 'Recogida',
        'delivery' => 'Envío',
        'bw' => 'B/N',
    ],

    'status' => [
        'pending' => 'Pendiente',
        'printing' => 'Imprimiendo',
        'ready' => 'Listo',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ],

    'status_comments' => [
        'printing' => 'Marcado como en impresión',
        'ready' => 'Marcado como listo',
        'delivered' => 'Marcado como entregado',
    ],

    'print_type' => [
        'bw' => 'Blanco y negro',
        'color' => 'Color',
    ],

    'delivery_method' => [
        'pickup' => 'Recogida en tienda',
        'delivery' => 'Envío a domicilio',
    ],

    'payment_status' => [
        'pending' => 'Pendiente',
        'paid' => 'Pagado',
        'failed' => 'Fallido',
    ],

    'tooltips' => [
        'downloaded_at' => 'Descargado el :date',
        'not_downloaded' => 'Aún no descargado',
    ],

    'actions' => [
        'mark_processing' => 'Marcar en impresión',
        'mark_ready' => 'Marcar listo',
        'mark_delivered' => 'Marcar entregado',
        'download_file' => 'Descargar archivo',
    ],

    'notifications' => [
        'status_updated' => 'Estado actualizado',
        'order_ready' => 'Pedido listo',
        'order_delivered' => 'Pedido entregado',
    ],

    'form' => [
        'sections' => [
            'customer_info' => 'Cliente',
            'print_specs' => 'Especificaciones de impresión',
            'delivery' => 'Entrega',
            'status_payment' => 'Estado y pago',
            'prices' => 'Precios',
            'notes' => 'Notas',
        ],
        'fields' => [
            'customer' => 'Cliente',
            'customer_name' => 'Nombre',
            'customer_email' => 'Correo',
            'customer_phone' => 'Teléfono',
            'print_type' => 'Tipo de impresión',
            'paper_size' => 'Tamaño de papel',
            'paper_type' => 'Tipo de papel',
            'orientation' => 'Orientación',
            'page_range' => 'Rango de páginas',
            'copies' => 'Copias',
            'pages_count' => 'Número de páginas',
            'double_sided' => 'Doble cara',
            'binding' => 'Encuadernación',
            'delivery_method' => 'Método de entrega',
            'branch' => 'Sucursal',
            'delivery_address' => 'Dirección de envío',
            'delivery_phone' => 'Teléfono de envío',
            'delivery_notes' => 'Notas de envío',
            'status' => 'Estado',
            'payment_method' => 'Método de pago',
            'payment_status' => 'Estado de pago',
            'price_per_page' => 'Precio por página',
            'binding_price' => 'Encuadernación',
            'double_sided_cost' => 'Costo doble cara',
            'subtotal' => 'Subtotal',
            'delivery_cost' => 'Envío',
            'tax' => 'Impuestos',
            'total' => 'Total',
            'customer_notes' => 'Notas del cliente',
            'admin_notes' => 'Notas internas',
        ],
        'placeholders' => [
            'customer_select' => 'Selecciona un cliente',
        ],
        'helpers' => [
            'customer_empty' => 'Elige un cliente registrado o completa los datos manualmente.',
            'page_range' => 'Ej.: 1-5, 8, 10-12',
        ],
        'no_locker' => [
            'title' => 'Sin casillero',
            'notice' => 'Este cliente no tiene casillero asignado.',
        ],
        'paper_size' => [
            'letter' => 'Carta',
            'legal' => 'Oficio',
            'a4' => 'A4',
        ],
        'paper_type' => [
            'standard' => 'Estándar',
            'bond' => 'Bond',
            'glossy' => 'Brillante',
        ],
        'orientation' => [
            'portrait' => 'Vertical',
            'landscape' => 'Horizontal',
        ],
        'delivery_method_options' => [
            'pickup' => 'Recogida',
            'delivery' => 'Envío',
        ],
        'status_options' => [
            'pending' => 'Pendiente',
            'printing' => 'Imprimiendo',
            'ready' => 'Listo',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ],
        'payment_method' => [
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'cash' => 'Efectivo',
        ],
    ],
];
