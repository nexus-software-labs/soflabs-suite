<?php

namespace App\Filament\Admin\Resources\Plans\Tables;

use App\Models\Plan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withCount('tenants'),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_monthly')
                    ->label('Mensual')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('price_yearly')
                    ->label('Anual')
                    ->money('USD')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('modules')
                    ->label('Módulos incluidos')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('—')
                    ->formatStateUsing(fn (mixed $state): mixed => $state ?? []),
                TextColumn::make('tenants_count')
                    ->label('Inquilinos')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicar')
                    ->excludeAttributes(attributes: [
                        'id',
                        'deleted_at',
                        'created_at',
                        'updated_at',
                        'tenants_count',
                    ])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $name = (string) ($data['name'] ?? 'plan');
                        $data['name'] = $name.' (copia)';

                        $base = Str::slug($data['name']);
                        $slug = $base;
                        $suffix = 0;

                        while (Plan::withTrashed()->where('slug', $slug)->exists()) {
                            ++$suffix;
                            $slug = $base.'-'.$suffix;
                        }

                        $data['slug'] = $slug;

                        return $data;
                    }),
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
