<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests\Pages;

use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use App\Services\Inventory\OutboundWorkflowService;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOutboundRequest extends ViewRecord
{
    protected static string $resource = OutboundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => $this->record->status === 'requested'),
            Action::make('reserve')
                ->label('Reservar')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === 'requested')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        app(OutboundWorkflowService::class)->reserve($this->record, auth()->id());
                        $this->refreshFormData(['status', 'reserved_at', 'processed_by']);
                        Notification::make()->title('Solicitud reservada correctamente')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title('No se pudo reservar')->body($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('dispatch')
                ->label('Despachar')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'reserved')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        app(OutboundWorkflowService::class)->dispatch($this->record, auth()->id());
                        $this->refreshFormData(['status', 'dispatched_at', 'processed_by']);
                        Notification::make()->title('Solicitud despachada correctamente')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title('No se pudo despachar')->body($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
