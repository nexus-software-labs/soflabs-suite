<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class InventoryPlugin implements Plugin
{
    public function getId(): string
    {
        return 'inventory';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
