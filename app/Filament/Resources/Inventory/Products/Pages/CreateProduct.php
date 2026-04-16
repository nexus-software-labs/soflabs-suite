<?php

namespace App\Filament\Resources\Inventory\Products\Pages;

use App\Filament\Resources\Inventory\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
