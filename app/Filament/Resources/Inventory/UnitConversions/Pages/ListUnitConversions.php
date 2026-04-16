<?php

namespace App\Filament\Resources\Inventory\UnitConversions\Pages;

use App\Filament\Resources\Inventory\UnitConversions\UnitConversionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnitConversions extends ListRecords
{
    protected static string $resource = UnitConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
