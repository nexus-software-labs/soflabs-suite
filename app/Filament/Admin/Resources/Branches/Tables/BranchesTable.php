<?php

namespace App\Filament\Admin\Resources\Branches\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['tenant']),
            )
            ->columns([
                TextColumn::make('tenant.company_name')
                    ->label('Inquilino')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas(
                            'tenant',
                            fn (Builder $q): Builder => $q
                                ->where('company_name', 'like', "%{$search}%")
                                ->orWhere('id', 'like', "%{$search}%"),
                        );
                    })
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->placeholder('—'),
                IconColumn::make('is_main')
                    ->label('Principal')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Inquilino')
                    ->relationship(
                        name: 'tenant',
                        titleAttribute: 'company_name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('company_name'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Tenant $record): string => filled($record->company_name)
                            ? (string) $record->company_name
                            : (string) $record->id,
                    )
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
