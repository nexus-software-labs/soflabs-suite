<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OutboundRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitud')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('request_number')
                                    ->label('Número')
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->colors([
                                        'warning' => 'requested',
                                        'info' => 'reserved',
                                        'success' => 'dispatched',
                                        'danger' => 'cancelled',
                                    ]),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega')
                                    ->placeholder('—'),
                                TextEntry::make('requested_by_name')
                                    ->label('Solicitado por')
                                    ->placeholder('—'),
                                TextEntry::make('destination')
                                    ->label('Destino')
                                    ->placeholder('—'),
                                TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->dateTime(),
                                TextEntry::make('reserved_at')
                                    ->label('Reservado')
                                    ->dateTime()
                                    ->placeholder('—'),
                                TextEntry::make('dispatched_at')
                                    ->label('Despachado')
                                    ->dateTime()
                                    ->placeholder('—'),
                                TextEntry::make('cancellation_reason')
                                    ->label('Motivo cancelación')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Líneas')
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->label('')
                            ->schema([
                                TextEntry::make('line_number')->label('#'),
                                TextEntry::make('product.sku')->label('SKU'),
                                TextEntry::make('product.name')->label('Producto'),
                                TextEntry::make('requested_quantity')
                                    ->label('Solicitada')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('reserved_quantity')
                                    ->label('Reservada')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('dispatched_quantity')
                                    ->label('Despachada')
                                    ->numeric(decimalPlaces: 4),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
