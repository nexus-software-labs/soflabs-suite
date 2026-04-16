<?php

namespace App\Filament\Resources\Inventory\SupplierProducts\Pages;

use App\Filament\Resources\Inventory\SupplierProducts\SupplierProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplierProduct extends ViewRecord
{
    protected static string $resource = SupplierProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
