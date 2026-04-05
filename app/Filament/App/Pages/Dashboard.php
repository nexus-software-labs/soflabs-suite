<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

final class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Panel principal';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    protected static string $routePath = '/dashboard';

    /**
     * @return array<class-string | \Filament\Widgets\WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [];
    }
}
