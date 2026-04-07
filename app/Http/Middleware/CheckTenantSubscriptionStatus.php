<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Subscriptions\TenantSubscription;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscriptionStatus
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $context = app(TenantContext::class);
        $subscription = $context->subscription;

        if ($subscription === null) {
            abort(402, 'No hay suscripción activa para este tenant.');
        }

        if ($subscription->status === TenantSubscription::STATUS_SUSPENDED || $subscription->status === TenantSubscription::STATUS_CANCELED) {
            abort(402, 'Tu suscripción está suspendida o cancelada.');
        }

        return $next($request);
    }
}
