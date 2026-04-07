<?php

namespace App\Filament\Admin\Resources\Payments\Pages;

use App\Events\PaymentCompleted;
use App\Filament\Admin\Resources\Payments\PaymentResource;
use App\Models\Core\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $record = $this->record;
        $canConfirm = in_array($record->gateway, ['transfer', 'cash'])
            && $record->status === Payment::STATUS_PENDING;

        if ($canConfirm) {
            $actions[] = Action::make('confirm_payment')
                ->label(__('payment.confirm_received'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading(__('payment.confirm_modal.heading'))
                ->modalDescription(__('payment.confirm_modal.description'))
                ->modalSubmitActionLabel(__('payment.confirm_modal.submit_label'))
                ->action(function () {
                    $this->record->markAsCompleted();
                    event(new PaymentCompleted($this->record));
                    $this->record->refresh();
                    Notification::make()
                        ->title(__('payment.confirm_modal.notification_title'))
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->load('paymentable');
    }
}
