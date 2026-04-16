<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Units\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unidad')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('abbreviation')
                            ->label('Abreviación')
                            ->required()
                            ->maxLength(20),
                        Select::make('unit_type')
                            ->label('Tipo')
                            ->options([
                                'weight' => 'Peso',
                                'volume' => 'Volumen',
                                'quantity' => 'Cantidad',
                                'length' => 'Longitud',
                            ])
                            ->default('quantity')
                            ->required(),
                        Toggle::make('is_system')
                            ->label('Unidad del sistema'),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activa',
                                'inactive' => 'Inactiva',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
