<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TenantAuthController extends Controller
{
    /**
     * Muestra el formulario de acceso Inertia para el inquilino actual.
     */
    public function showLogin(TenantContext $tenantContext): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $tenant = $tenantContext->tenant;
        abort_unless($tenant instanceof Tenant, 404);

        $tenant->loadMissing('domains');

        return Inertia::render('Auth/Login', [
            'tenant' => [
                'name' => $tenant->domains->first()?->domain ?? $tenant->id,
                'company_name' => $tenant->company_name ?? '',
            ],
        ]);
    }

    /**
     * Autentica un usuario del inquilino actual (misma conexión central que Fortify).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $currentTenantId = tenant()?->id;
        abort_unless($currentTenantId !== null, 404);

        if (! Auth::attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ],
            $request->boolean('remember'),
        )) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas o no perteneces a este espacio.',
            ])->onlyInput('email');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->tenant_id !== $currentTenantId) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Credenciales incorrectas o no perteneces a este espacio.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Cierra la sesión web. Usado por {@see \Filament\Auth\Http\Controllers\LogoutController}
     * en paneles Filament (mismo guard web).
     */
    public function logout(Request $request): LogoutResponseContract
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return app(LogoutResponseContract::class);
    }
}
