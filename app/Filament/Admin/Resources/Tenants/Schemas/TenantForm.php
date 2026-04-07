<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la empresa')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nombre de la empresa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('id')
                            ->label('Identificador (subdominio)')
                            ->helperText('Será el subdominio del inquilino. Solo letras minúsculas y guiones (por ejemplo: mi-empresa).')
                            ->visibleOn('create')
                            ->required()
                            ->maxLength(255)
                            ->rule('regex:/^[a-z]+(-[a-z]+)*$/')
                            ->unique(table: (new Tenant)->getTable(), column: 'id')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state === null ? null : Str::lower($state)),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),
                        Select::make('country')
                            ->label('País')
                            ->options([
                                'SV' => 'El Salvador',
                                'GT' => 'Guatemala',
                                'HN' => 'Honduras',
                                'MX' => 'México',
                                'CO' => 'Colombia',
                                'US' => 'Estados Unidos',
                            ])
                            ->nullable()
                            ->searchable(),
                    ])
                    ->columns(2),
                Section::make('Plan y estado')
                    ->schema([
                        Select::make('plan_id')
                            ->label('Plan')
                            ->relationship(
                                name: 'plan',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->where('is_active', true),
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('db_mode')
                            ->label('Modo de base de datos')
                            ->options([
                                'shared' => 'Compartida (shared)',
                                'schema' => 'Esquema (schema)',
                                'dedicated' => 'Dedicada (dedicated)',
                            ])
                            ->required()
                            ->default('shared')
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                        DateTimePicker::make('trial_ends_at')
                            ->label('Fin del periodo de prueba')
                            ->nullable()
                            ->seconds(false),
                        DateTimePicker::make('subscribed_at')
                            ->label('Fecha de suscripción')
                            ->nullable()
                            ->seconds(false),
                    ])
                    ->columns(2),
                Section::make('Módulos activos')
                    ->schema([
                        CheckboxList::make('active_modules')
                            ->label('Módulos')
                            ->options([
                                'inventory' => 'Inventario',
                                'packages' => 'Paquetería / logística',
                                'printing' => 'Impresiones',
                            ])
                            ->columns(3)
                            ->dehydrated(false)
                            ->afterStateHydrated(function (CheckboxList $component, $state): void {
                                $record = $component->getRecord();

                                if (! $record instanceof Tenant) {
                                    return;
                                }

                                $active = $record->modules()
                                    ->where('is_active', true)
                                    ->pluck('module')
                                    ->all();

                                $component->state($active);
                            }),
                    ]),
            ]);
    }
}
