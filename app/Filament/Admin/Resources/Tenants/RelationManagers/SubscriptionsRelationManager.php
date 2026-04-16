<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Suscripciones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('billing_cycle')
                    ->label('Ciclo'),
                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge(),
                TextColumn::make('plan.name')
                    ->label('Plan'),
                TextColumn::make('ends_at')
                    ->label('Vence')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
