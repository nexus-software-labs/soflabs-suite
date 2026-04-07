<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\Page;

class PurchaseRecurrenceReportPage extends Page
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.pages.purchase-recurrence-report';

    protected static ?string $title = 'Reporte de recurrencia de compras';
}
