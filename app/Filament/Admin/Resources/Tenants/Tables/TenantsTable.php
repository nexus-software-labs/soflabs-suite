<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

use App\Filament\Admin\Resources\Tenants\Actions\ImpersonateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with('plan')->withCount([
                    'modules as active_modules_count' => fn (Builder $q): Builder => $q->where('is_active', true),
                ]),
            )
            ->columns([
                TextColumn::make('company_name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('id')
                    ->label('Subdominio')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->sortable(),
                TextColumn::make('db_mode')
                    ->label('Modo DB')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'shared' => 'shared',
                        'schema' => 'schema',
                        'dedicated' => 'dedicated',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'shared' => 'gray',
                        'schema' => 'warning',
                        'dedicated' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('active_modules_count')
                    ->label('Módulos activos')
                    ->sortable(),
                TextColumn::make('subscription_status')
                    ->label('Suscripción')
                    ->state(fn ($record): string => (string) ($record->subscriptions()->latest('created_at')->value('status') ?? 'sin_suscripcion'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'past_due' => 'warning',
                        'suspended', 'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->where('is_active', true))
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
                SelectFilter::make('db_mode')
                    ->label('Modo DB')
                    ->options([
                        'shared' => 'shared',
                        'schema' => 'schema',
                        'dedicated' => 'dedicated',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ImpersonateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
