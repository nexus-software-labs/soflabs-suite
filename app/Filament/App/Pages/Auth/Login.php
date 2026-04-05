<?php

declare(strict_types=1);

namespace App\Filament\App\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

final class Login extends BaseLogin
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        if (! tenancy()->initialized) {
            throw ValidationException::withMessages([
                'data.email' => 'No tienes acceso a este espacio de trabajo.',
            ]);
        }

        $tenantId = tenant()->id;

        $user = User::query()->where('email', $data['email'] ?? '')->first();
        if ($user !== null && $user->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'data.email' => 'No tienes acceso a este espacio de trabajo.',
            ]);
        }

        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
