<?php

namespace App\Filament\Admin\Resources\PrintOrders\Pages;

use App\Filament\Admin\Resources\PrintOrders\PrintOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrintOrder extends EditRecord
{
    protected static string $resource = PrintOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
