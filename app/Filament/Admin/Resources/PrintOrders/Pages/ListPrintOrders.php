<?php

namespace App\Filament\Admin\Resources\PrintOrders\Pages;

use App\Filament\Admin\Resources\PrintOrders\PrintOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPrintOrders extends ListRecords
{
    protected static string $resource = PrintOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('print_order.tabs.all')),

            'pending' => Tab::make(__('print_order.tabs.pending'))
                ->query(fn ($query) => $query->where('status', 'pending'))
                ->badge(fn () => static::getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'delivered' => Tab::make(__('print_order.tabs.delivered'))
                ->query(fn ($query) => $query->where('status', 'delivered'))
                ->badge(fn () => static::getModel()::where('status', 'delivered')->count())
                ->badgeColor('success'),
        ];
    }
}
