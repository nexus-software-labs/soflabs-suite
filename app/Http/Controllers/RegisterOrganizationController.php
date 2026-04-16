<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterOrganizationRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Onboarding\RegisterOrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterOrganizationController extends Controller
{
    public function create(): Response
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'description',
                'price_monthly',
                'price_yearly',
                'currency',
                'trial_period',
                'trial_interval',
                'modules',
            ]);

        return Inertia::render('auth/register-organization', [
            'plans' => $plans,
            'moduleOptions' => [
                ['key' => 'inventory', 'label' => __('Inventario')],
                ['key' => 'packages', 'label' => __('Paquetería / logística')],
                ['key' => 'printing', 'label' => __('Impresiones')],
            ],
            'appDomain' => (string) config('app.domain'),
        ]);
    }

    public function store(RegisterOrganizationRequest $request, RegisterOrganizationService $registerOrganizationService): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = $registerOrganizationService->register($request->validated());

        $target = $this->tenantPanelLoginUrl($tenant).'?registered=1';

        return redirect()->away($target);
    }

    private function tenantPanelLoginUrl(Tenant $tenant): string
    {
        $domain = config('app.domain');
        if (! is_string($domain) || $domain === '') {
            return URL::to('/panel/login');
        }

        $base = config('app.url');
        $scheme = 'https';
        if (is_string($base) && str_contains($base, '://')) {
            $parsed = parse_url($base);
            if (is_array($parsed) && isset($parsed['scheme']) && is_string($parsed['scheme'])) {
                $scheme = $parsed['scheme'];
            }
        }

        return sprintf('%s://%s.%s/panel/login', $scheme, $tenant->getKey(), $domain);
    }
}
