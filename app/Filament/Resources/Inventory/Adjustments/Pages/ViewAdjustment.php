<?php

namespace App\Filament\Resources\Inventory\Adjustments\Pages;

use App\Filament\Resources\Inventory\Adjustments\AdjustmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdjustment extends ViewRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
