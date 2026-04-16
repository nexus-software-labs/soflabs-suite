<?php

namespace App\Filament\Resources\Inventory\OutboundRequests\Pages;

use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOutboundRequest extends EditRecord
{
    protected static string $resource = OutboundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
