<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntakeDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('document_number')
                                    ->label('Número')
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->colors([
                                        'gray' => 'received',
                                        'warning' => ['processing', 'review'],
                                        'success' => 'approved',
                                        'danger' => 'rejected',
                                    ]),
                                TextEntry::make('origin')
                                    ->label('Origen')
                                    ->badge(),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega')
                                    ->placeholder('—'),
                                TextEntry::make('supplier.name')
                                    ->label('Proveedor')
                                    ->placeholder('—'),
                                TextEntry::make('document_date')
                                    ->label('Fecha documento')
                                    ->date()
                                    ->placeholder('—'),
                                TextEntry::make('subtotal')->money('USD'),
                                TextEntry::make('tax')->money('USD'),
                                TextEntry::make('total')->money('USD'),
                                TextEntry::make('ai_confidence')
                                    ->label('Confianza AI')
                                    ->numeric(decimalPlaces: 4)
                                    ->placeholder('—'),
                                TextEntry::make('rejection_reason')
                                    ->label('Motivo rechazo')
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
                                TextEntry::make('description_original')->label('Descripción'),
                                TextEntry::make('product.sku')->label('SKU')->placeholder('—'),
                                TextEntry::make('product.name')->label('Producto')->placeholder('—'),
                                TextEntry::make('quantity')->label('Cantidad')->numeric(decimalPlaces: 4),
                                TextEntry::make('unit.name')->label('Unidad')->placeholder('—'),
                                TextEntry::make('unit_price')->label('Precio')->money('USD')->placeholder('—'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('USD')->placeholder('—'),
                                TextEntry::make('status')->label('Estado')->badge(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
