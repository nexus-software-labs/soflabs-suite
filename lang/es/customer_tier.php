<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Categorías de cliente',
    'model_label' => 'categoría',
    'plural_model_label' => 'Categorías de cliente',

    'sections' => [
        'general_info' => 'Información general',
    ],

    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'description' => 'Descripción',
        'color' => 'Color',
        'icon' => 'Icono',
        'priority' => 'Prioridad',
        'is_active' => 'Activa',
    ],

    'placeholders' => [
        'name' => 'Nombre visible',
        'slug' => 'identificador-url',
        'description' => 'Opcional',
        'icon' => 'heroicon-o-star',
    ],

    'helpers' => [
        'slug' => 'Sin espacios; se usa en integraciones.',
        'color' => 'Color del distintivo en listados.',
        'icon' => 'Nombre de icono Heroicons.',
        'priority' => 'Mayor número = más prioridad.',
        'is_active' => 'Las categorías inactivas no se asignan a nuevos clientes.',
    ],

    'table' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'priority' => 'Prioridad',
        'customers_count' => 'Clientes',
        'benefits_count' => 'Beneficios',
        'state' => 'Estado',
        'created' => 'Creada',
        'updated' => 'Actualizada',
    ],

    'filters' => [
        'state' => 'Estado',
        'all' => 'Todos',
        'active' => 'Activas',
        'inactive' => 'Inactivas',
    ],

    'actions' => [
        'activate_selected' => 'Activar seleccionadas',
        'deactivate_selected' => 'Desactivar seleccionadas',
    ],

    'benefits' => [
        'title' => 'Beneficios',
        'model_label' => 'beneficio',
        'plural_model_label' => 'Beneficios',

        'sections' => [
            'info' => 'Información',
            'type' => 'Tipo de beneficio',
            'scope' => 'Alcance',
            'scope_description' => 'Dónde aplica este beneficio.',
            'config' => 'Configuración',
        ],

        'fields' => [
            'name' => 'Nombre',
            'description' => 'Descripción',
            'discount_type' => 'Tipo de descuento',
            'applies_to' => 'Aplica a',
            'discount_percentage' => 'Porcentaje',
            'fixed_rate' => 'Tarifa fija',
            'discount_fixed' => 'Monto fijo',
            'max_discount_amount' => 'Descuento máximo',
            'scope_type' => 'Tipo de alcance',
            'region' => 'Región',
            'country' => 'País',
            'branches' => 'Sucursales',
            'service_type' => 'Servicios',
            'min_order_amount' => 'Monto mínimo',
            'priority' => 'Prioridad',
            'is_active' => 'Activo',
        ],

        'placeholders' => [
            'name' => 'Nombre del beneficio',
            'description' => 'Descripción breve',
        ],

        'helpers' => [
            'description' => 'Ayuda interna para el equipo.',
            'fixed_rate_applies' => 'Tarifa fija aplica por libra de peso.',
            'fixed_rate_value' => 'Precio en $/lb.',
            'max_discount' => 'Tope con descuento porcentual.',
            'region' => 'Solo sucursales en esta región.',
            'country' => 'Solo sucursales en este país.',
            'branches' => 'Selecciona sucursales concretas.',
            'min_order' => 'Pedido mínimo para aplicar el beneficio.',
            'priority' => 'Orden entre beneficios de la misma categoría.',
            'is_active' => 'Desactiva sin eliminar.',
        ],

        'discount_types' => [
            'percentage' => 'Porcentaje',
            'fixed_amount' => 'Monto fijo',
            'fixed_rate' => 'Tarifa fija ($/lb)',
        ],

        'applies_to' => [
            'delivery' => 'Envío',
            'subtotal' => 'Subtotal',
            'weight' => 'Peso',
        ],

        'scope_types' => [
            'all' => 'Todas las sucursales',
            'region' => 'Por región',
            'country' => 'Por país',
            'branches' => 'Sucursales específicas',
        ],

        'service_types' => [
            'both' => 'Todos',
            'print_order' => 'Impresión',
            'pre_alert' => 'Pre-alerta',
        ],

        'table' => [
            'name' => 'Nombre',
            'discount_type' => 'Tipo',
            'applies_to' => 'Aplica a',
            'value' => 'Valor',
            'scope' => 'Alcance',
            'services' => 'Servicios',
            'priority' => 'Prioridad',
            'state' => 'Activo',
            'created_at' => 'Creado',
        ],

        'table_discount_types' => [
            'percentage' => 'Porcentaje',
            'fixed_amount' => 'Monto fijo',
            'fixed_rate' => 'Tarifa fija',
        ],

        'table_applies_to' => [
            'delivery' => 'Envío',
            'subtotal' => 'Subtotal',
            'weight' => 'Peso',
        ],

        'table_scope' => [
            'all' => 'Global',
            'region' => 'Región',
            'country' => 'País',
            'branches' => 'Sucursales',
        ],

        'table_services' => [
            'both' => 'Todos',
            'print_order' => 'Impresión',
            'pre_alert' => 'Pre-alerta',
        ],

        'filters' => [
            'state' => 'Estado',
            'all' => 'Todos',
            'active' => 'Activos',
            'inactive' => 'Inactivos',
            'discount_type' => 'Tipo',
            'scope' => 'Alcance',
            'services' => 'Servicio',
        ],

        'filter_scope' => [
            'all' => 'Global',
            'region' => 'Región',
            'country' => 'País',
            'branches' => 'Sucursales',
        ],
    ],
];
