<?php

declare(strict_types=1);

return [
    'title' => 'Direcciones',
    'model_label' => 'dirección',
    'plural_model_label' => 'Direcciones',

    'fields' => [
        'name' => 'Nombre / alias',
        'country' => 'País',
        'region_code' => 'Departamento / estado',
        'city_code' => 'Municipio / ciudad',
        'locality_code' => 'Localidad',
        'address' => 'Dirección',
        'references' => 'Referencias',
        'phone' => 'Teléfono',
        'is_default' => 'Predeterminada',
    ],

    'placeholders' => [
        'name' => 'Casa, oficina, etc.',
    ],

    'countries' => [
        'SV' => 'El Salvador',
        'GT' => 'Guatemala',
        'HN' => 'Honduras',
        'NI' => 'Nicaragua',
        'CR' => 'Costa Rica',
        'PA' => 'Panamá',
        'US' => 'Estados Unidos',
        'MX' => 'México',
    ],

    'table' => [
        'name' => 'Nombre',
        'country' => 'País',
        'city' => 'Ciudad',
        'address' => 'Dirección',
        'phone' => 'Teléfono',
        'is_default' => 'Predeterminada',
        'created_at' => 'Creada',
    ],

    'filters' => [
        'is_default' => 'Predeterminada',
        'all' => 'Todas',
        'yes' => 'Sí',
        'no' => 'No',
    ],
];
