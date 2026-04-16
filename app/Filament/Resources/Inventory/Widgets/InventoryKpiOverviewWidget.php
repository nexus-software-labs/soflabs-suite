<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Widgets;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use App\Filament\Resources\Inventory\Movements\MovementResource;
use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use App\Filament\Resources\Inventory\Stocks\StockResource;
use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\Stock;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class InventoryKpiOverviewWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Resumen de inventario';

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $lowStockCount = Stock::query()
            ->join('inventory_products', 'inventory_products.id', '=', 'inventory_stocks.product_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'inventory_products.minimum_stock')
            ->count();

        $reviewDocuments = IntakeDocument::query()
            ->where('status', 'review')
            ->count();

        $reservedOutbound = OutboundRequest::query()
            ->where('status', 'reserved')
            ->count();

        $todayMovements = DB::table('inventory_movements')
            ->whereDate('moved_at', today())
            ->count();

        return [
            Stat::make('Stock bajo mínimo', (string) $lowStockCount)
                ->description('Productos que requieren reposición')
                ->color('danger')
                ->url(StockResource::getUrl('index')),
            Stat::make('Entradas en revisión', (string) $reviewDocuments)
                ->description('Documentos pendientes de aprobación')
                ->color('warning')
                ->url(IntakeDocumentResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'review'],
                    ],
                ])),
            Stat::make('Salidas reservadas', (string) $reservedOutbound)
                ->description('Pendientes de despacho')
                ->color('info')
                ->url(OutboundRequestResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'reserved'],
                    ],
                ])),
            Stat::make('Movimientos hoy', (string) $todayMovements)
                ->description('Registros operativos del día')
                ->color('success')
                ->url(MovementResource::getUrl('index')),
        ];
    }
}
