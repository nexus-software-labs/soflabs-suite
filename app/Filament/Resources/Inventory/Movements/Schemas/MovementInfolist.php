<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Movements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Movimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('moved_at')
                                    ->label('Fecha')
                                    ->dateTime(),
                                TextEntry::make('movement_type')
                                    ->label('Tipo')
                                    ->badge()
                                    ->colors([
                                        'success' => ['inbound', 'adjustment_increase'],
                                        'danger' => ['outbound', 'adjustment_decrease'],
                                    ]),
                                TextEntry::make('reference_type')
                                    ->label('Tipo referencia')
                                    ->placeholder('—'),
                                TextEntry::make('reference_id')
                                    ->label('Id referencia')
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('product.sku')
                                    ->label('SKU')
                                    ->placeholder('—'),
                                TextEntry::make('product.name')
                                    ->label('Producto')
                                    ->placeholder('—'),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega')
                                    ->placeholder('—'),
                                TextEntry::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('stock_before')
                                    ->label('Stock anterior')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('stock_after')
                                    ->label('Stock final')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('performer.name')
                                    ->label('Ejecutado por')
                                    ->placeholder('—'),
                                TextEntry::make('notes')
                                    ->label('Notas')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
