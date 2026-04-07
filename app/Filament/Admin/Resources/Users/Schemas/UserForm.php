<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isTenantPanel = Filament::getCurrentPanel()?->getId() === 'app';

        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: (new User)->getTable(),
                                column: 'email',
                                ignoreRecord: true,
                            ),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
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
                            ->live()
                            ->nullable()
                            ->helperText('Vacío = superadministrador (sin inquilino asignado).')
                            ->afterStateUpdated(fn (Set $set) => $set('branch_id', null))
                            ->hidden($isTenantPanel),
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->relationship(
                                name: 'branch',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get) use ($isTenantPanel): Builder {
                                    if ($isTenantPanel) {
                                        $tenantKey = tenant()?->getTenantKey();

                                        if (! filled($tenantKey)) {
                                            return $query->whereRaw('0 = 1');
                                        }

                                        return $query->where(
                                            $query->getModel()->getTable().'.tenant_id',
                                            $tenantKey,
                                        );
                                    }

                                    $tenantId = $get('tenant_id');

                                    if (! filled($tenantId)) {
                                        return $query->whereRaw('0 = 1');
                                    }

                                    return $query->where(
                                        $query->getModel()->getTable().'.tenant_id',
                                        $tenantId,
                                    );
                                },
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Toggle::make('is_tenant_admin')
                            ->label('Administrador del inquilino')
                            ->default(false),
                        Toggle::make('is_super_admin')
                            ->label('Superadministrador')
                            ->default(false)
                            ->hidden($isTenantPanel),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
