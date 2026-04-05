<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Tenant;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        /** @var Tenant $record */
        $record = $this->record;

        TenantResource::afterCreate($record, $this->form->getRawState());
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }
}
