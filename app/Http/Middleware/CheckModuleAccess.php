<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $context = app(TenantContext::class);

        if (! tenancy()->initialized || ! in_array($module, $context->modules, true)) {
            abort(403, 'Módulo no disponible en tu plan.');
        }

        return $next($request);
    }
}
