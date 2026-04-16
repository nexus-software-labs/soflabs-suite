<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proveedor')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('tax_id')
                            ->label('NIT / Tax ID')
                            ->maxLength(80),
                        Select::make('supplier_type')
                            ->label('Tipo')
                            ->options([
                                'manufacturer' => 'Fabricante',
                                'distributor' => 'Distribuidor',
                                'local' => 'Local',
                                'international' => 'Internacional',
                            ])
                            ->default('local')
                            ->required(),
                        TextInput::make('country_code')
                            ->label('País (ISO)')
                            ->maxLength(10),
                        TextInput::make('payment_terms')
                            ->label('Términos de pago')
                            ->maxLength(120),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
