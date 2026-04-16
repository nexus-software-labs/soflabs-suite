<?php

namespace App\Filament\Resources\Inventory\Families\Pages;

use App\Filament\Resources\Inventory\Families\FamilyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFamily extends ViewRecord
{
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
