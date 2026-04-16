<?php

namespace App\Filament\Resources\Inventory\UnitConversions\Pages;

use App\Filament\Resources\Inventory\UnitConversions\UnitConversionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUnitConversion extends EditRecord
{
    protected static string $resource = UnitConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
