<?php

namespace App\Filament\Admin\Widgets\Admin;

use App\Models\SubscriptionAlert;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionAlerts extends TableWidget
{
    protected static ?string $heading = 'Alertas de suscripción recientes';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SubscriptionAlert::query()->latest('created_at'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('tenant_id')
                    ->label('Tenant')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('level')
                    ->label('Nivel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(80)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Nivel')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Danger',
                    ]),
            ])
            ->paginated([10, 25, 50]);
    }
}
