<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Tenant;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Validator;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function beforeValidate(): void
    {
        $state = $this->form->getRawState();
        $email = trim((string) ($state['admin_email'] ?? ''));
        if ($email === '') {
            return;
        }

        Validator::make($state, [
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();
    }

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
