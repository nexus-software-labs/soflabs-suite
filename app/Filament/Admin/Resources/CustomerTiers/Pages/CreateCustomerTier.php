<?php

namespace App\Filament\Admin\Resources\CustomerTiers\Pages;

use App\Filament\Admin\Resources\CustomerTiers\CustomerTierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerTier extends CreateRecord
{
    protected static string $resource = CustomerTierResource::class;
}
