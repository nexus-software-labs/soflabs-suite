<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Alta self-service de organización: crea {@see Tenant} y ejecuta el mismo aprovisionamiento que el panel central.
 */
final class RegisterOrganizationService
{
    public function __construct(
        private readonly TenantOnboardingOrchestrator $tenantOnboardingOrchestrator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): Tenant
    {
        $tenantId = Str::lower(trim((string) ($data['id'] ?? '')));
        if ($tenantId === '') {
            throw ValidationException::withMessages([
                'id' => __('El identificador del inquilino es obligatorio.'),
            ]);
        }

        if (Tenant::query()->whereKey($tenantId)->exists()) {
            throw ValidationException::withMessages([
                'id' => __('Este subdominio ya está en uso.'),
            ]);
        }

        return DB::transaction(function () use ($data, $tenantId): Tenant {
            $plan = Plan::query()->find($data['plan_id']);

            /** @var Tenant $tenant */
            $tenant = Tenant::query()->create([
                'id' => $tenantId,
                'company_name' => (string) $data['company_name'],
                'plan_id' => $data['plan_id'],
                'db_mode' => (string) ($data['db_mode'] ?? 'shared'),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'trial_ends_at' => $data['trial_ends_at'] ?? self::computeTrialEndsAt($plan),
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
            ]);

            $tenant->load('plan');

            $rawFormState = [
                'active_modules' => $data['active_modules'] ?? [],
                'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
                'billing_gateway' => $data['billing_gateway'] ?? 'cash',
                'admin_name' => $data['admin_name'] ?? '',
                'admin_email' => $data['admin_email'] ?? '',
                'admin_password' => $data['admin_password'] ?? '',
                'admin_password_confirmation' => $data['admin_password_confirmation'] ?? '',
            ];

            $this->tenantOnboardingOrchestrator->provisionAfterTenantCreated($tenant, $rawFormState);

            return $tenant->fresh();
        });
    }

    private static function computeTrialEndsAt(?Plan $plan): ?Carbon
    {
        if ($plan === null || ! $plan->hasTrial()) {
            return null;
        }

        $period = (int) ($plan->trial_period ?? 0);
        $interval = (string) ($plan->trial_interval ?? 'day');

        return match ($interval) {
            'month' => now()->addMonths($period),
            'year' => now()->addYears($period),
            default => now()->addDays($period),
        };
    }
}
