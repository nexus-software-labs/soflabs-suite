<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Movements\Pages;

use App\Filament\Resources\Inventory\Movements\MovementResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMovement extends ViewRecord
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
