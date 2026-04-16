<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Widgets;

use App\Models\Inventory\Movement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class InventoryMovementsTrendWidget extends ChartWidget
{
    protected ?string $heading = 'Tendencia de movimientos (14 días)';

    protected function getData(): array
    {
        $labels = [];
        $inbound = [];
        $outbound = [];

        /** @var array<string, array{inbound: float, outbound: float}> $bucket */
        $bucket = [];

        $startDate = now()->subDays(13)->startOfDay();

        for ($i = 0; $i < 14; $i++) {
            $date = $startDate->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $bucket[$key] = ['inbound' => 0.0, 'outbound' => 0.0];
        }

        $rows = Movement::query()
            ->selectRaw('DATE(moved_at) as move_date, movement_type, SUM(quantity) as total_quantity')
            ->where('moved_at', '>=', $startDate)
            ->groupBy('move_date', 'movement_type')
            ->get();

        foreach ($rows as $row) {
            $key = Carbon::parse($row->move_date)->toDateString();
            if (! array_key_exists($key, $bucket)) {
                continue;
            }

            $quantity = (float) $row->total_quantity;
            if (in_array($row->movement_type, ['inbound', 'adjustment_increase'], true)) {
                $bucket[$key]['inbound'] += $quantity;
            }

            if (in_array($row->movement_type, ['outbound', 'adjustment_decrease'], true)) {
                $bucket[$key]['outbound'] += $quantity;
            }
        }

        foreach ($bucket as $values) {
            $inbound[] = $values['inbound'];
            $outbound[] = $values['outbound'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $inbound,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                ],
                [
                    'label' => 'Salidas',
                    'data' => $outbound,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
