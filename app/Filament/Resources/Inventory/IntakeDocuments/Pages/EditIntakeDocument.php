<?php

namespace App\Filament\Resources\Inventory\IntakeDocuments\Pages;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIntakeDocument extends EditRecord
{
    protected static string $resource = IntakeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
