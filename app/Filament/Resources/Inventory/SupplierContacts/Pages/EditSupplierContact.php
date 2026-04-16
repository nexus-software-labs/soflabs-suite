<?php

namespace App\Filament\Resources\Inventory\SupplierContacts\Pages;

use App\Filament\Resources\Inventory\SupplierContacts\SupplierContactResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplierContact extends EditRecord
{
    protected static string $resource = SupplierContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
