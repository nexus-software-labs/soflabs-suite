<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Models\Core\CustomerTierHistory;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Obtener el cliente actual
        $customer = $this->record;
        $previousTierId = $customer->customer_tier_id;
        $newTierId = $data['customer_tier_id'] ?? null;

        // Si la categoría cambió, guardar la referencia para usar después del save
        if ($previousTierId != $newTierId) {
            $this->previousTierId = $previousTierId;
            $this->newTierId = $newTierId;
        } else {
            // Limpiar si no hay cambio
            unset($this->previousTierId);
            unset($this->newTierId);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Registrar cambio de categoría en historial si hubo cambio
        if (isset($this->previousTierId) && isset($this->newTierId)) {
            $customer = $this->record->fresh();
            $previousTierId = $this->previousTierId;
            $newTierId = $this->newTierId;

            // Registrar en historial
            CustomerTierHistory::create([
                'customer_id' => $customer->id,
                'customer_tier_id' => $newTierId,
                'previous_tier_id' => $previousTierId,
                'change_reason' => 'manual',
                'notes' => 'Cambio realizado desde el panel de administración',
                'changed_by' => auth()->id(),
            ]);

            // Limpiar referencias
            unset($this->previousTierId);
            unset($this->newTierId);
        }
    }
}
