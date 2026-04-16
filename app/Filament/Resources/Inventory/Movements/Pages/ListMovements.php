<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Movements\Pages;

use App\Filament\Resources\Inventory\Movements\MovementResource;
use App\Models\Inventory\Movement;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                    $handle = fopen('php://output', 'wb');

                    fputcsv($handle, [
                        'fecha',
                        'tipo',
                        'sku',
                        'producto',
                        'bodega',
                        'cantidad',
                        'stock_antes',
                        'stock_despues',
                        'tipo_referencia',
                        'id_referencia',
                    ]);

                    Movement::query()
                        ->with(['product', 'warehouse'])
                        ->orderByDesc('moved_at')
                        ->chunk(500, function ($movements) use ($handle): void {
                            foreach ($movements as $movement) {
                                fputcsv($handle, [
                                    $movement->moved_at?->toDateTimeString(),
                                    $movement->movement_type,
                                    $movement->product?->sku,
                                    $movement->product?->name,
                                    $movement->warehouse?->name,
                                    $movement->quantity,
                                    $movement->stock_before,
                                    $movement->stock_after,
                                    $movement->reference_type,
                                    $movement->reference_id,
                                ]);
                            }
                        });

                    fclose($handle);
                }, 'inventory_kardex.csv')),
        ];
    }
}
