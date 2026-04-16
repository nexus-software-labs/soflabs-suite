<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Adjustments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ajuste')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('adjusted_at')
                                    ->label('Fecha')
                                    ->dateTime(),
                                TextEntry::make('adjustment_type')
                                    ->label('Tipo')
                                    ->badge()
                                    ->colors([
                                        'success' => 'positive',
                                        'danger' => 'negative',
                                    ]),
                                TextEntry::make('difference_quantity')
                                    ->label('Diferencia')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('product.sku')
                                    ->label('SKU')
                                    ->placeholder('—'),
                                TextEntry::make('product.name')
                                    ->label('Producto')
                                    ->placeholder('—'),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega')
                                    ->placeholder('—'),
                                TextEntry::make('movement.id')
                                    ->label('Movimiento')
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('performer.name')
                                    ->label('Ejecutado por')
                                    ->placeholder('—'),
                                TextEntry::make('evidence_path')
                                    ->label('Evidencia')
                                    ->placeholder('—'),
                                TextEntry::make('reason')
                                    ->label('Motivo')
                                    ->columnSpanFull(),
                                TextEntry::make('notes')
                                    ->label('Notas')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
