<?php

namespace App\Filament\Resources\Inventory\IntakeDocuments\Pages;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIntakeDocuments extends ListRecords
{
    protected static string $resource = IntakeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
