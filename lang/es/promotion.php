<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Promociones',
    'model_label' => 'promoción',
    'plural_model_label' => 'Promociones',

    'sections' => [
        'promotion_info' => 'Información general',
        'application_type' => 'Tipo de aplicación',
        'discount_type' => 'Tipo de descuento',
        'scope' => 'Alcance',
        'scope_description' => 'Define a qué clientes o ubicaciones aplica la promoción.',
        'config' => 'Configuración y vigencia',
    ],

    'fields' => [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'application_type' => 'Aplicación',
        'coupon_code' => 'Código de cupón',
        'usage_limit' => 'Límite de usos',
        'discount_type' => 'Tipo de descuento',
        'applies_to' => 'Aplica a',
        'max_discount_amount' => 'Descuento máximo',
        'scope_type' => 'Tipo de alcance',
        'customer_tier_id' => 'Categoría de cliente',
        'region_id' => 'Región',
        'country_id' => 'País',
        'branches' => 'Sucursales',
        'customers' => 'Clientes',
        'service_type' => 'Servicios',
        'min_order_amount' => 'Monto mínimo de pedido',
        'starts_at' => 'Inicio',
        'expires_at' => 'Fin',
        'active' => 'Activa',
    ],

    'placeholders' => [
        'name' => 'Nombre de la promoción',
        'description' => 'Describe la promoción',
        'coupon_code' => 'Código único',
        'usage_limit' => 'Ilimitado si está vacío',
        'all_categories' => 'Todas las categorías',
        'no_email' => 'sin correo',
        'no_name' => 'Sin nombre',
        'all' => 'Todas',
    ],

    'helpers' => [
        'description' => 'Visible para el equipo interno.',
        'coupon_code' => 'Los clientes ingresarán este código al pagar.',
        'usage_limit' => 'Número máximo de veces que se puede usar el cupón.',
        'applies_to_free_delivery' => 'Envío gratis aplica al costo de envío.',
        'fixed_rate_value' => 'Precio fijo por libra de peso.',
        'max_discount' => 'Tope cuando el descuento es porcentual.',
        'customer_tier' => 'Opcional: limita por categoría de cliente.',
        'region' => 'Solo pedidos de sucursales en esta región.',
        'country' => 'Solo pedidos de sucursales en este país.',
        'branches' => 'Selecciona una o más sucursales.',
        'customers' => 'Solo estos clientes podrán usar la promoción.',
        'min_order_amount' => 'Pedido mínimo para activar la promoción.',
        'active' => 'Desactiva para pausar sin eliminar.',
    ],

    'application_types' => [
        'automatic' => 'Automática',
        'coupon' => 'Cupón',
    ],

    'discount_types' => [
        'free_delivery' => 'Envío gratis',
        'percentage' => 'Porcentaje',
        'fixed_amount' => 'Monto fijo',
        'fixed_rate' => 'Tarifa fija ($/lb)',
    ],

    'discount_labels' => [
        'percentage' => 'Porcentaje de descuento',
        'fixed' => 'Monto de descuento',
    ],

    'applies_to_options' => [
        'delivery' => 'Envío',
        'subtotal' => 'Subtotal',
        'weight' => 'Peso',
    ],

    'scope_types' => [
        'all' => 'Todas las sucursales',
        'region' => 'Por región',
        'country' => 'Por país',
        'branches' => 'Sucursales específicas',
        'customers' => 'Clientes específicos',
    ],

    'service_types' => [
        'both' => 'Todos los servicios',
        'print_order' => 'Solo impresión',
        'pre_alert' => 'Solo pre-alerta',
    ],

    'table' => [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'value' => 'Valor',
        'scope' => 'Alcance',
        'category' => 'Categoría',
        'active' => 'Activa',
        'starts_at' => 'Inicio',
        'expires_at' => 'Fin',
        'usages' => 'Usos',
    ],

    'table_scope' => [
        'all' => 'Global',
        'region' => 'Región',
        'country' => 'País',
        'branches' => 'Sucursales',
        'customers' => 'Clientes',
    ],

    'filters' => [
        'state' => 'Estado',
        'active' => 'Activas',
        'inactive' => 'Inactivas',
        'discount_type' => 'Tipo de descuento',
        'scope' => 'Alcance',
        'customer_tier' => 'Categoría',
        'current' => 'Vigentes',
        'expired' => 'Expiradas',
    ],
];
