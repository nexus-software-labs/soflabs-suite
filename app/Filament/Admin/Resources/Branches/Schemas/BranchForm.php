<?php

namespace App\Filament\Admin\Resources\Branches\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sucursal')
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Inquilino')
                            ->relationship(
                                name: 'tenant',
                                titleAttribute: 'company_name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->orderBy('company_name')
                                    ->orderBy('id'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Tenant $record): string => filled($record->company_name)
                                    ? (string) $record->company_name
                                    : (string) $record->id,
                            )
                            ->searchable(['company_name', 'id'])
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(120)
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
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('is_main')
                            ->label('Sucursal principal')
                            ->helperText('Solo puede haber una sucursal principal por tenant')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                        KeyValue::make('settings')
                            ->label('Ajustes')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->addActionLabel('Añadir')
                            ->nullable()
                            ->dehydrateStateUsing(function (?array $state): ?array {
                                if ($state === null || $state === []) {
                                    return null;
                                }

                                return $state;
                            })
                            ->afterStateHydrated(function (KeyValue $component, $state): void {
                                if ($state === null) {
                                    $component->state([]);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
