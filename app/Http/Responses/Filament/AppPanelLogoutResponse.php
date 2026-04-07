<?php

declare(strict_types=1);

namespace App\Http\Responses\Filament;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

/**
 * Tras el logout, Filament intenta resolver la URL de login; en el panel app
 * el dominio incluye {tenant} y {@see Filament::getLoginUrl()}
 * puede fallar sin parámetros. Redirigimos al login del panel en el mismo host.
 */
final class AppPanelLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $panel = Filament::getCurrentPanel();

        if ($panel !== null && $panel->getId() === 'app') {
            return redirect('/panel/login');
        }

        return redirect()->to(
            Filament::hasLogin() ? Filament::getLoginUrl() : Filament::getUrl(),
        );
    }
}
