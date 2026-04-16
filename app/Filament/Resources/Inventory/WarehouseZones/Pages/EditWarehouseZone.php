<?php

namespace App\Filament\Resources\Inventory\WarehouseZones\Pages;

use App\Filament\Resources\Inventory\WarehouseZones\WarehouseZoneResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseZone extends EditRecord
{
    protected static string $resource = WarehouseZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
