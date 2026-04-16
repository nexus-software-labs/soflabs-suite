<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\SupplierContacts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SupplierContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contacto proveedor')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship(
                                name: 'supplier',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('job_title')
                            ->label('Cargo')
                            ->maxLength(120),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(120),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(50),
                        Select::make('contact_type')
                            ->label('Tipo contacto')
                            ->options([
                                'sales' => 'Ventas',
                                'billing' => 'Facturación',
                                'support' => 'Soporte',
                                'general' => 'General',
                            ])
                            ->default('general'),
                        Toggle::make('is_primary')
                            ->label('Principal'),
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
