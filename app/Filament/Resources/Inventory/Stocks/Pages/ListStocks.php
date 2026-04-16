<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Stocks\Pages;

use App\Filament\Resources\Inventory\Stocks\StockResource;
use App\Models\Inventory\Stock;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                    $handle = fopen('php://output', 'wb');

                    fputcsv($handle, ['sku', 'producto', 'bodega', 'existencia', 'reservado', 'actualizado']);

                    Stock::query()
                        ->with(['product', 'warehouse'])
                        ->orderByDesc('updated_at')
                        ->chunk(500, function ($stocks) use ($handle): void {
                            foreach ($stocks as $stock) {
                                fputcsv($handle, [
                                    $stock->product?->sku,
                                    $stock->product?->name,
                                    $stock->warehouse?->name,
                                    $stock->quantity,
                                    $stock->reserved_quantity,
                                    $stock->updated_at?->toDateTimeString(),
                                ]);
                            }
                        });

                    fclose($handle);
                }, 'inventory_stock.csv')),
        ];
    }
}
