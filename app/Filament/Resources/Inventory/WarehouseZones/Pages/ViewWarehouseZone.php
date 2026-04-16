<?php

namespace App\Filament\Resources\Inventory\WarehouseZones\Pages;

use App\Filament\Resources\Inventory\WarehouseZones\WarehouseZoneResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouseZone extends ViewRecord
{
    protected static string $resource = WarehouseZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
