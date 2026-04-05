<?php

declare(strict_types=1);

namespace App\Modules\Packages\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class PackagesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'packages';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
