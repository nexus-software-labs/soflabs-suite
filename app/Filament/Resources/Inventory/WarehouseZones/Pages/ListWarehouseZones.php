<?php

namespace App\Filament\Resources\Inventory\WarehouseZones\Pages;

use App\Filament\Resources\Inventory\WarehouseZones\WarehouseZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseZones extends ListRecords
{
    protected static string $resource = WarehouseZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
