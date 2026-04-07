<?php

declare(strict_types=1);

namespace App\Http\Middleware\Filament;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * El panel Filament "app" registra rutas en el dominio <code>{tenant}.APP_DOMAIN</code>.
 * Laravel exige el parámetro de ruta <code>tenant</code> al llamar a <code>route()</code>;
 * sin {@see URL::defaults()} los redirects (p. ej. al login) lanzan UrlGenerationException.
 */
final class ShareTenantParameterForFilamentUrls
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->tenantSlug($request);

        if ($slug !== null) {
            URL::defaults(['tenant' => $slug]);
        }

        return $next($request);
    }

    private function tenantSlug(Request $request): ?string
    {
        $tenant = $request->route()?->parameter('tenant');
        if (is_string($tenant) && $tenant !== '') {
            return $tenant;
        }

        $central = config('app.domain');
        if (! is_string($central) || $central === '') {
            return null;
        }

        $host = $request->getHost();
        $suffix = '.'.$central;

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $sub = substr($host, 0, -strlen($suffix));

        return $sub !== '' ? $sub : null;
    }
}
