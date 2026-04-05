<?php

declare(strict_types=1);

namespace App\Modules\Printing\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class PrintingPlugin implements Plugin
{
    public function getId(): string
    {
        return 'printing';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
