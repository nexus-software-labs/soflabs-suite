<?php

namespace App\Filament\Resources\Inventory\UnitConversions\Pages;

use App\Filament\Resources\Inventory\UnitConversions\UnitConversionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUnitConversion extends ViewRecord
{
    protected static string $resource = UnitConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
