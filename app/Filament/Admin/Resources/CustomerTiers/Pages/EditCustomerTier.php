<?php

namespace App\Filament\Admin\Resources\CustomerTiers\Pages;

use App\Filament\Admin\Resources\CustomerTiers\CustomerTierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerTier extends EditRecord
{
    protected static string $resource = CustomerTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
