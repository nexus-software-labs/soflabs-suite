<?php

declare(strict_types=1);

namespace App\Filament\App\Plugins;

use App\Http\Middleware\Filament\PrepareFilamentPanelContext;
use App\Modules\Inventory\Filament\InventoryPlugin;
use App\Modules\Packages\Filament\PackagesPlugin;
use App\Modules\Printing\Filament\PrintingPlugin;
use App\Services\TenantContext;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registra en caliente los plugins de módulo cuando el panel hace boot,
 * con TenantContext ya rellenado por {@see PrepareFilamentPanelContext}.
 * Los recursos con rutas que deben existir al cargar rutas van en el panel
 * provider; aquí solo se aplica lo que depende del contexto (p. ej. widgets).
 */
final class TenantModulePluginsRegistration implements Plugin
{
    public function getId(): string
    {
        return 'tenant-module-plugins';
    }

    public function register(Panel $panel): void
    {
        $panel->bootUsing(function (Panel $panel): void {
            foreach (self::pluginsForContext(app(TenantContext::class)) as $modulePlugin) {
                $modulePlugin->register($panel);
                $modulePlugin->boot($panel);
            }
        });
    }

    public function boot(Panel $panel): void {}

    /**
     * @return list<Plugin>
     */
    public static function pluginsForContext(TenantContext $context): array
    {
        /** @var array<string, class-string<Plugin>> $map */
        $map = config('modules.filament_plugins', [
            'inventory' => InventoryPlugin::class,
            'packages' => PackagesPlugin::class,
            'printing' => PrintingPlugin::class,
        ]);

        $plugins = [];
        $seen = [];
        foreach ($context->modules as $module) {
            if (! isset($map[$module])) {
                continue;
            }

            $class = $map[$module];
            if (isset($seen[$class])) {
                continue;
            }

            if (! class_exists($class)) {
                continue;
            }

            $seen[$class] = true;
            $plugins[] = app($class);
        }

        return $plugins;
    }
}
