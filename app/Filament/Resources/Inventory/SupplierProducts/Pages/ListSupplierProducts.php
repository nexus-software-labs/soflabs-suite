<?php

namespace App\Filament\Resources\Inventory\SupplierProducts\Pages;

use App\Filament\Resources\Inventory\SupplierProducts\SupplierProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplierProducts extends ListRecords
{
    protected static string $resource = SupplierProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
