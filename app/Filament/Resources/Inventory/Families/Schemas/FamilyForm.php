<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Families\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FamilyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Familia')
                    ->schema([
                        Select::make('section_id')
                            ->label('Sección')
                            ->relationship(
                                name: 'section',
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
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activa',
                                'inactive' => 'Inactiva',
                            ])
                            ->default('active')
                            ->required(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
