<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Países',
    'model_label' => 'país',
    'plural_model_label' => 'Países',

    'sections' => [
        'general_info' => 'Información general',
        'regional_config' => 'Configuración regional',
        'pre_alert_calculation' => 'Cálculo pre-alerta',
    ],

    'fields' => [
        'region' => 'Región',
        'region_name' => 'Nombre región',
        'region_code' => 'Código región',
        'name' => 'Nombre',
        'code' => 'Código ISO',
        'is_active' => 'Activo',
        'currency_code' => 'Moneda',
        'timezone' => 'Zona horaria',
        'length_unit' => 'Unidad de longitud',
        'mass_unit' => 'Unidad de masa',
        'shipping_pound_value' => 'Valor libra envío',
        'customs_management' => 'Gestión aduanera',
        'third_party_handling' => 'Manejo terceros',
        'delivery_guarantee_percentage' => '% garantía entrega',
        'iva_cif_percentage' => '% IVA CIF',
        'dai_percentage' => '% DAI',
        'dai_threshold' => 'Umbral DAI',
    ],

    'placeholders' => [
        'name' => 'Nombre del país',
        'code' => 'Ej. SV',
    ],

    'helpers' => [
        'code' => 'Código de dos letras.',
        'length_unit' => 'Usado en cotizaciones.',
        'mass_unit' => 'Peso en pre-alerta.',
        'shipping_pound_value' => 'Costo referencia por libra.',
        'customs_management' => 'Costos aduaneros.',
        'third_party_handling' => 'Handling de terceros.',
        'delivery_guarantee' => 'Porcentaje de garantía.',
        'iva_cif' => 'Impuesto sobre valor CIF.',
        'dai' => 'Arancel DAI.',
        'dai_threshold' => 'Monto mínimo para aplicar DAI.',
    ],

    'currencies' => [
        'USD' => 'Dólar (USD)',
        'EUR' => 'Euro (EUR)',
        'MXN' => 'Peso mexicano',
        'GTQ' => 'Quetzal',
        'HNL' => 'Lempira',
        'NIO' => 'Córdoba',
        'CRC' => 'Colón costarricense',
        'PAB' => 'Balboa',
        'BRL' => 'Real brasileño',
        'ARS' => 'Peso argentino',
        'CLP' => 'Peso chileno',
        'COP' => 'Peso colombiano',
        'PEN' => 'Sol',
    ],

    'timezones' => [
        'America/El_Salvador' => 'El Salvador',
        'America/Guatemala' => 'Guatemala',
        'America/Tegucigalpa' => 'Honduras',
        'America/Managua' => 'Nicaragua',
        'America/Costa_Rica' => 'Costa Rica',
        'America/Panama' => 'Panamá',
        'America/Mexico_City' => 'México',
        'America/Bogota' => 'Colombia',
        'America/Lima' => 'Perú',
        'America/Santiago' => 'Chile',
        'America/Argentina/Buenos_Aires' => 'Argentina',
        'America/Sao_Paulo' => 'Brasil',
    ],

    'length_units' => [
        'cm' => 'Centímetros',
        'm' => 'Metros',
        'in' => 'Pulgadas',
    ],

    'mass_units' => [
        'kg' => 'Kilogramos',
        'g' => 'Gramos',
        'lb' => 'Libras',
    ],

    'table' => [
        'region' => 'Región',
        'name' => 'Nombre',
        'code' => 'Código',
        'currency_code' => 'Moneda',
        'timezone' => 'Zona horaria',
        'branches_count' => 'Sucursales',
        'is_active' => 'Activo',
        'created_at' => 'Creado',
    ],

    'filters' => [
        'region' => 'Región',
        'state' => 'Estado',
        'all' => 'Todos',
        'active_only' => 'Solo activos',
        'inactive_only' => 'Solo inactivos',
        'currency' => 'Moneda',
    ],

    'table_actions' => [
        'activate_selected' => 'Activar seleccionados',
        'deactivate_selected' => 'Desactivar seleccionados',
    ],
];
