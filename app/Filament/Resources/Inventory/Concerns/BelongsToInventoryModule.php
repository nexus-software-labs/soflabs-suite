<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Concerns;

use App\Services\TenantContext;

trait BelongsToInventoryModule
{
    public static function shouldRegisterNavigation(): bool
    {
        if (! static::$shouldRegisterNavigation) {
            return false;
        }

        return app(TenantContext::class)->hasModule('inventory');
    }

    public static function canAccess(): bool
    {
        if (! app(TenantContext::class)->hasModule('inventory')) {
            return false;
        }

        return static::canViewAny();
    }
}
