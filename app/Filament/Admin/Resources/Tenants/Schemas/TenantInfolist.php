<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la empresa')
                    ->schema([
                        TextEntry::make('company_name')->label('Nombre de la empresa'),
                        TextEntry::make('id')->label('Subdominio')->badge(),
                        TextEntry::make('phone')->label('Teléfono')->placeholder('—'),
                        TextEntry::make('country')
                            ->label('País')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'SV' => 'El Salvador',
                                'GT' => 'Guatemala',
                                'HN' => 'Honduras',
                                'MX' => 'México',
                                'CO' => 'Colombia',
                                'US' => 'Estados Unidos',
                                default => $state ?? '—',
                            }),
                    ])
                    ->columns(2),
                Section::make('Plan y estado')
                    ->schema([
                        TextEntry::make('plan.name')->label('Plan'),
                        TextEntry::make('db_mode')
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
                            }),
                        IconEntry::make('is_active')->label('Activo')->boolean(),
                        TextEntry::make('trial_ends_at')->label('Fin de prueba')->dateTime()->placeholder('—'),
                        TextEntry::make('subscribed_at')->label('Suscripción')->dateTime()->placeholder('—'),
                        TextEntry::make('subscription_status')
                            ->label('Estado suscripción')
                            ->state(fn (Tenant $record): string => (string) ($record->subscriptions()->latest('created_at')->value('status') ?? 'sin_suscripcion'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                TenantSubscription::STATUS_ACTIVE => 'success',
                                TenantSubscription::STATUS_PAST_DUE => 'warning',
                                TenantSubscription::STATUS_SUSPENDED,
                                TenantSubscription::STATUS_CANCELED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('next_billing_at')
                            ->label('Próximo cobro')
                            ->state(fn (Tenant $record) => $record->subscriptions()->latest('created_at')->value('next_billing_at'))
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Resumen')
                    ->schema([
                        TextEntry::make('users_count')
                            ->label('Usuarios')
                            ->state(fn (Tenant $record): int => User::query()->where('tenant_id', $record->getKey())->count()),
                        TextEntry::make('branches_count')
                            ->label('Sucursales')
                            ->state(fn (Tenant $record): int => $record->branches()->count()),
                        RepeatableEntry::make('activeModules')
                            ->label('Módulos activos')
                            ->schema([
                                TextEntry::make('module')->label('Módulo'),
                                TextEntry::make('activated_at')->label('Activado el')->dateTime()->placeholder('—'),
                            ])
                            ->state(fn (Tenant $record) => $record->modules()
                                ->where('is_active', true)
                                ->orderBy('module')
                                ->get()),
                    ])
                    ->columns(1),
            ]);
    }
}
