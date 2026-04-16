<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Módulos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module')
                    ->label('Módulo')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('activated_at')
                    ->label('Activado')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->paginated(false);
    }
}
