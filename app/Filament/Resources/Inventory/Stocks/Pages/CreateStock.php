<?php

namespace App\Filament\Resources\Inventory\Stocks\Pages;

use App\Filament\Resources\Inventory\Stocks\StockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;
}
