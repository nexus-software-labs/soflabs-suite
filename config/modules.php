<?php

declare(strict_types=1);

use App\Modules\Inventory\Filament\InventoryPlugin;
use App\Modules\Packages\Filament\PackagesPlugin;
use App\Modules\Printing\Filament\PrintingPlugin;
use Filament\Contracts\Plugin;

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Modules Registry
    |--------------------------------------------------------------------------
    |
    | Single source of truth for tenant module slugs and integration points.
    | Keep keys and technical identifiers in English to stay globally consistent.
    |
    */
    'tenant_routes' => [
        'inventory' => [
            'prefix' => 'inventory',
            'name_prefix' => 'inventory.',
            'dashboard_page' => 'Inventory/Dashboard',
        ],
        'packages' => [
            'prefix' => 'packages',
            'name_prefix' => 'packages.',
            'dashboard_page' => 'Packages/Dashboard',
        ],
        'printing' => [
            'prefix' => 'printing',
            'name_prefix' => 'printing.',
            'dashboard_page' => 'Printing/Dashboard',
        ],
    ],

    /** @var array<string, class-string<Plugin>> */
    'filament_plugins' => [
        'inventory' => InventoryPlugin::class,
        'packages' => PackagesPlugin::class,
        'printing' => PrintingPlugin::class,
    ],
];
