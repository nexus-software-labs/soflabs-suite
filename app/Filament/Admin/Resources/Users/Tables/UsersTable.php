<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['tenant', 'branch']),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tenant_display')
                    ->label('Inquilino')
                    ->getStateUsing(function (User $record): string {
                        if ($record->tenant_id === null) {
                            return 'Superadmin';
                        }

                        return (string) ($record->tenant?->company_name ?? $record->tenant_id);
                    })
                    ->badge()
                    ->color(fn (User $record): string => $record->tenant_id === null ? 'warning' : 'gray'),
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_tenant_admin')
                    ->label('Admin inquilino')
                    ->boolean(),
                IconColumn::make('is_super_admin')
                    ->label('Superadmin')
                    ->boolean(),
                TextColumn::make('last_seen_at')
                    ->label('Última actividad')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Inquilino')
                    ->relationship(
                        name: 'tenant',
                        titleAttribute: 'company_name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->orderBy('company_name'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Tenant $record): string => filled($record->company_name)
                            ? (string) $record->company_name
                            : (string) $record->id,
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'superadmin' => 'Superadministrador',
                        'tenant_admin' => 'Administrador de inquilino',
                        'miembro' => 'Usuario de inquilino',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $role = $data['value'] ?? null;

                        if (blank($role)) {
                            return;
                        }

                        match ($role) {
                            'superadmin' => $query->where('is_super_admin', true),
                            'tenant_admin' => $query->where('is_tenant_admin', true)
                                ->where('is_super_admin', false),
                            'miembro' => $query->whereNotNull('tenant_id')
                                ->where('is_tenant_admin', false)
                                ->where('is_super_admin', false),
                            default => null,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
