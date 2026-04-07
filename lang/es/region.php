<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Regiones',
    'model_label' => 'región',
    'plural_model_label' => 'Regiones',

    'sections' => [
        'general_info' => 'Información general',
    ],

    'fields' => [
        'name' => 'Nombre',
        'code' => 'Código',
        'franchisee_id' => 'Franquiciado',
        'is_active' => 'Activa',
        'description' => 'Descripción',
    ],

    'placeholders' => [
        'name' => 'Nombre de la región',
        'code' => 'Código único',
        'franchisee' => 'Seleccionar',
        'description' => 'Opcional',
    ],

    'helpers' => [
        'code' => 'Identificador corto (ej. CAM).',
        'franchisee' => 'Responsable regional.',
        'is_active' => 'Las regiones inactivas no aparecen en selecciones.',
    ],

    'table' => [
        'name' => 'Nombre',
        'code' => 'Código',
        'franchisee_local' => 'Franquiciado local',
        'franchisee_regional' => 'Franquiciado regional',
        'countries_count' => 'Países',
        'branches_count' => 'Sucursales',
        'is_active' => 'Activa',
        'created_at' => 'Creada',
        'updated_at' => 'Actualizada',
    ],

    'table_placeholders' => [
        'unassigned' => 'Sin asignar',
    ],

    'filters' => [
        'franchisee' => 'Franquiciado',
        'state' => 'Estado',
        'all' => 'Todos',
        'active_only' => 'Solo activas',
        'inactive_only' => 'Solo inactivas',
    ],
];
