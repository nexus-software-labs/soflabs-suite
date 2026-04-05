<?php

declare(strict_types=1);

namespace App\Http\Middleware\Filament;

use App\Http\Middleware\InjectTenantContext;
use Closure;
use Filament\Http\Middleware\SetUpPanel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ejecuta identificación de tenancy e inyección de TenantContext antes de
 * {@see SetUpPanel}, para que los plugins del panel app vean módulos activos.
 */
final class PrepareFilamentPanelContext
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $panel): mixed
    {
        $setUpPanel = app(SetUpPanel::class);

        if ($panel === 'app') {
            $tenancyMiddleware = config('tenancy.identification_middleware');

            return app($tenancyMiddleware)->handle(
                $request,
                fn (Request $request): mixed => app(InjectTenantContext::class)->handle(
                    $request,
                    fn (Request $request): mixed => $setUpPanel->handle($request, $next, $panel),
                ),
            );
        }

        return app(InjectTenantContext::class)->handle(
            $request,
            fn (Request $request): mixed => $setUpPanel->handle($request, $next, $panel),
        );
    }
}
