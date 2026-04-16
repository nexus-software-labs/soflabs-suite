<?php

namespace App\Filament\Resources\Inventory\Adjustments\Pages;

use App\Filament\Resources\Inventory\Adjustments\AdjustmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdjustments extends ListRecords
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
