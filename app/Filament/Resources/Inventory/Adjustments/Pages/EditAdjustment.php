<?php

namespace App\Filament\Resources\Inventory\Adjustments\Pages;

use App\Filament\Resources\Inventory\Adjustments\AdjustmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdjustment extends EditRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
