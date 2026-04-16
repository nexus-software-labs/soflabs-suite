<?php

namespace App\Filament\Resources\Inventory\Stocks\Pages;

use App\Filament\Resources\Inventory\Stocks\StockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStock extends ViewRecord
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
