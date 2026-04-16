<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests\Tables;

use App\Services\Inventory\OutboundWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OutboundRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['warehouse', 'creator', 'processor', 'lines']),
            )
            ->columns([
                TextColumn::make('request_number')
                    ->label('Solicitud')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requested_by_name')
                    ->label('Solicitó')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'warning' => 'requested',
                        'info' => 'reserved',
                        'success' => 'dispatched',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('lines_count')
                    ->counts('lines')
                    ->label('Líneas'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Solicitada',
                        'reserved' => 'Reservada',
                        'dispatched' => 'Despachada',
                        'cancelled' => 'Cancelada',
                    ]),
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Bodega')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label('Fecha creación')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde'),
                        DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record): bool => $record->status === 'requested'),
                Action::make('reserve')
                    ->label('Reservar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn ($record): bool => $record->status === 'requested')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        try {
                            app(OutboundWorkflowService::class)->reserve($record, auth()->id());
                            Notification::make()
                                ->title('Solicitud reservada correctamente')
                                ->success()
                                ->send();
                        } catch (DomainException $exception) {
                            Notification::make()
                                ->title('No se pudo reservar la solicitud')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('dispatch')
                    ->label('Despachar')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'reserved')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        try {
                            app(OutboundWorkflowService::class)->dispatch($record, auth()->id());
                            Notification::make()
                                ->title('Solicitud despachada correctamente')
                                ->success()
                                ->send();
                        } catch (DomainException $exception) {
                            Notification::make()
                                ->title('No se pudo despachar la solicitud')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reserveSelected')
                        ->label('Reservar seleccionados')
                        ->icon('heroicon-o-lock-closed')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $processed = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'requested') {
                                    $errors++;

                                    continue;
                                }

                                try {
                                    app(OutboundWorkflowService::class)->reserve($record, auth()->id());
                                    $processed++;
                                } catch (DomainException) {
                                    $errors++;
                                }
                            }

                            $notification = Notification::make()
                                ->title("Reservadas: {$processed} · Omitidas/Error: {$errors}");

                            if ($errors > 0) {
                                $notification->warning()->send();

                                return;
                            }

                            $notification->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('dispatchSelected')
                        ->label('Despachar seleccionados')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $processed = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'reserved') {
                                    $errors++;

                                    continue;
                                }

                                try {
                                    app(OutboundWorkflowService::class)->dispatch($record, auth()->id());
                                    $processed++;
                                } catch (DomainException) {
                                    $errors++;
                                }
                            }

                            $notification = Notification::make()
                                ->title("Despachadas: {$processed} · Omitidas/Error: {$errors}");

                            if ($errors > 0) {
                                $notification->warning()->send();

                                return;
                            }

                            $notification->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => false),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
