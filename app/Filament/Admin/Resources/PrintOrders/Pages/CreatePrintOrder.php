<?php

namespace App\Filament\Admin\Resources\PrintOrders\Pages;

use App\Filament\Admin\Resources\PrintOrders\PrintOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrintOrder extends CreateRecord
{
    protected static string $resource = PrintOrderResource::class;
}
