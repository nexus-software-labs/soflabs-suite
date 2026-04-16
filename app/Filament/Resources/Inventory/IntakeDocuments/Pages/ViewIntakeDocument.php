<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments\Pages;

use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use App\Services\Inventory\IntakeDocumentWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewIntakeDocument extends ViewRecord
{
    protected static string $resource = IntakeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => in_array($this->record->status, ['review', 'received', 'processing'], true)),
            Action::make('queueAi')
                ->label('Procesar con AI')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->visible(fn (): bool => in_array($this->record->status, ['received', 'processing'], true))
                ->requiresConfirmation()
                ->action(function (): void {
                    app(IntakeDocumentWorkflowService::class)->queueAiProcessing($this->record);
                    Notification::make()->title('Documento enviado a procesamiento AI')->success()->send();
                }),
            Action::make('approve')
                ->label('Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'review')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        app(IntakeDocumentWorkflowService::class)->approve($this->record, auth()->id());
                        $this->refreshFormData(['status', 'approved_at', 'approved_by']);
                        Notification::make()->title('Documento aprobado correctamente')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title('No se pudo aprobar')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('reject')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => in_array($this->record->status, ['review', 'processing', 'received'], true))
                ->form([
                    Textarea::make('reason')
                        ->label('Motivo de rechazo')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    app(IntakeDocumentWorkflowService::class)->reject($this->record, (string) $data['reason']);
                    $this->refreshFormData(['status', 'rejected_at', 'rejection_reason']);
                    Notification::make()->title('Documento rechazado')->warning()->send();
                }),
        ];
    }
}
