<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments\Tables;

use App\Services\Inventory\IntakeDocumentWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IntakeDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with(['supplier', 'warehouse', 'creator', 'lines']),
            )
            ->columns([
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'gray' => 'received',
                        'warning' => ['processing', 'review'],
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('origin')
                    ->label('Origen')
                    ->badge(),
                TextColumn::make('lines_count')
                    ->counts('lines')
                    ->label('Líneas')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'received' => 'Recibido',
                        'processing' => 'Procesando',
                        'review' => 'En revisión',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ]),
                SelectFilter::make('origin')
                    ->options([
                        'manual' => 'Manual',
                        'ai' => 'AI',
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
                    ->visible(fn ($record): bool => in_array($record->status, ['review', 'received', 'processing'], true)),
                Action::make('queueAi')
                    ->label('Procesar con AI')
                    ->icon('heroicon-o-sparkles')
                    ->color('gray')
                    ->visible(fn ($record): bool => in_array($record->status, ['received', 'processing'], true))
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        app(IntakeDocumentWorkflowService::class)->queueAiProcessing($record);

                        Notification::make()
                            ->title('Documento enviado a procesamiento AI')
                            ->success()
                            ->send();
                    }),
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'review')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        try {
                            app(IntakeDocumentWorkflowService::class)->approve($record, auth()->id());

                            Notification::make()
                                ->title('Documento aprobado correctamente')
                                ->success()
                                ->send();
                        } catch (DomainException $exception) {
                            Notification::make()
                                ->title('No se pudo aprobar el documento')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record): bool => in_array($record->status, ['review', 'processing', 'received'], true))
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo de rechazo')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data): void {
                        app(IntakeDocumentWorkflowService::class)->reject($record, (string) $data['reason']);

                        Notification::make()
                            ->title('Documento rechazado')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Aprobar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $approved = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'review') {
                                    $errors++;

                                    continue;
                                }

                                try {
                                    app(IntakeDocumentWorkflowService::class)->approve($record, auth()->id());
                                    $approved++;
                                } catch (DomainException) {
                                    $errors++;
                                }
                            }

                            $notification = Notification::make()
                                ->title("Aprobados: {$approved} · Omitidos/Error: {$errors}");

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
