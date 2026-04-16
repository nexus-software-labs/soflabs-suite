<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Warehouses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bodega')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(120),
                        Select::make('warehouse_type')
                            ->label('Tipo')
                            ->options([
                                'main' => 'Principal',
                                'secondary' => 'Secundaria',
                                'transit' => 'Tránsito',
                            ])
                            ->default('main')
                            ->required(),
                        TextInput::make('location')
                            ->label('Ubicación')
                            ->maxLength(200),
                        Select::make('responsible_user_id')
                            ->label('Responsable')
                            ->relationship(
                                name: 'responsibleUser',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                            )
                            ->searchable()
                            ->preload(),
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
