<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Actions;

use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

final class ImpersonateAction
{
    public static function make(): Action
    {
        return Action::make('impersonateTenant')
            ->label('Abrir tenant')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->url(fn (Tenant $record): string => sprintf(
                'https://%s.%s/dashboard',
                $record->getKey(),
                config('app.domain')
            ))
            ->openUrlInNewTab();
    }
}
