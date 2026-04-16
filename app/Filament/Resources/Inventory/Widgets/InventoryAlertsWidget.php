<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Widgets;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use App\Filament\Resources\Inventory\Stocks\StockResource;
use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\Stock;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class InventoryAlertsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Alertas operativas';

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $overReserved = Stock::query()
            ->whereColumn('reserved_quantity', '>', 'quantity')
            ->count();

        $rejectedIntakes = IntakeDocument::query()
            ->where('status', 'rejected')
            ->where('rejected_at', '>=', now()->subDays(7))
            ->count();

        $stuckReserved = OutboundRequest::query()
            ->where('status', 'reserved')
            ->where('reserved_at', '<=', now()->subDays(2))
            ->count();

        $missingProductLinks = DB::table('inventory_intake_document_lines')
            ->where('status', 'pending_review')
            ->whereNull('product_id')
            ->count();

        return [
            Stat::make('Reserva > existencia', (string) $overReserved)
                ->description('Registros de stock inconsistentes')
                ->color($overReserved > 0 ? 'danger' : 'success')
                ->url(StockResource::getUrl('index', [
                    'tableFilters' => [
                        'has_reserved' => ['isActive' => true],
                    ],
                ])),
            Stat::make('Entradas rechazadas (7d)', (string) $rejectedIntakes)
                ->description('Documentos con incidencia reciente')
                ->color($rejectedIntakes > 0 ? 'warning' : 'success')
                ->url(IntakeDocumentResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'rejected'],
                    ],
                ])),
            Stat::make('Salidas reservadas > 48h', (string) $stuckReserved)
                ->description('Pendientes de despacho fuera de SLA')
                ->color($stuckReserved > 0 ? 'warning' : 'success')
                ->url(OutboundRequestResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'reserved'],
                    ],
                ])),
            Stat::make('Líneas sin producto', (string) $missingProductLinks)
                ->description('Pendientes de vinculación en revisión')
                ->color($missingProductLinks > 0 ? 'danger' : 'success')
                ->url(IntakeDocumentResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'review'],
                    ],
                ])),
        ];
    }
}
