<?php

namespace App\Filament\Resources\Inventory\OutboundRequests\Pages;

use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutboundRequests extends ListRecords
{
    protected static string $resource = OutboundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
