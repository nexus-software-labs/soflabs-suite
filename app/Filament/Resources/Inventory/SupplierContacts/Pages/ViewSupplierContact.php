<?php

namespace App\Filament\Resources\Inventory\SupplierContacts\Pages;

use App\Filament\Resources\Inventory\SupplierContacts\SupplierContactResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplierContact extends ViewRecord
{
    protected static string $resource = SupplierContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
