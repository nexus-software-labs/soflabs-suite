<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectTenantContext
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);

        if (tenancy()->initialized) {
            $tenant = tenant();
            if ($tenant instanceof Tenant) {
                $context->tenant = $tenant;
                $context->modules = $tenant->modules()
                    ->where('is_active', true)
                    ->pluck('module')
                    ->all();
            }
        }

        $user = $request->user();
        if ($user !== null) {
            $context->user = $user;
            $context->branch = $user->branch_id !== null
                ? Branch::query()->find($user->branch_id)
                : null;
        }

        return $next($request);
    }
}
