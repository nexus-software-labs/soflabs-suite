<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Filament\Admin\Resources\Tenants\Actions\ProvisionTenantAction;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Subscriptions\SubscriptionService;

/**
 * Pasos posteriores a la creación de un {@see Tenant}: dominio/sucursal/módulos, suscripción y usuario admin.
 */
final class TenantOnboardingOrchestrator
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly ProvisionTenantAdminUser $provisionTenantAdminUser,
    ) {}

    /**
     * @param  array<string, mixed>  $rawFormState  Misma forma que el formulario Filament (active_modules, billing_cycle, billing_gateway, admin_*).
     */
    public function provisionAfterTenantCreated(Tenant $tenant, array $rawFormState): void
    {
        ProvisionTenantAction::execute($tenant, $rawFormState);

        $billingCycle = (string) ($rawFormState['billing_cycle'] ?? 'monthly');
        $gateway = $this->resolveGatewayForSubscription($tenant, $rawFormState, $billingCycle);

        if ($tenant->plan !== null) {
            $this->subscriptionService->createSubscription(
                tenant: $tenant,
                plan: $tenant->plan,
                billingCycle: $billingCycle,
                gateway: $gateway,
            );
        }

        $this->provisionTenantAdminUser->provision($tenant, $rawFormState);
    }

    /**
     * @param  array<string, mixed>  $rawFormState
     */
    private function resolveGatewayForSubscription(Tenant $tenant, array $rawFormState, string $billingCycle): ?string
    {
        $plan = $tenant->plan;
        if (! $plan instanceof Plan) {
            return null;
        }

        if ($plan->getPriceForCycle($billingCycle) <= 0.0) {
            return null;
        }

        if ($plan->hasTrial()) {
            return null;
        }

        $gateway = (string) ($rawFormState['billing_gateway'] ?? 'cash');

        return $gateway !== '' ? $gateway : null;
    }
}
