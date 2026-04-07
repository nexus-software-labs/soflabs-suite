<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\Actions\ImportCustomersAction;
use App\Filament\Admin\Resources\Customers\CustomerResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recurrence-report')
                ->label('Reporte de recurrencia de compras')
                ->icon('heroicon-o-arrow-path')
                ->url(PurchaseRecurrenceReportPage::getUrl())
                ->color('success'),
            CreateAction::make(),
            ImportCustomersAction::make(),
        ];
    }
}
