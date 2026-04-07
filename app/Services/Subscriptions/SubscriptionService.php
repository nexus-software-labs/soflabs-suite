<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Core\Payment;
use App\Models\Plan;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubscriptionService
{
    private const RETRY_DAYS = [1, 3, 7];

    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly SubscriptionAlertService $alertService,
    ) {}

    public function createSubscription(
        Tenant $tenant,
        Plan $plan,
        string $billingCycle = 'monthly',
        ?string $gateway = null,
    ): TenantSubscription {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle, $gateway): TenantSubscription {
            $subscription = TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'subscriber_type' => Tenant::class,
                'subscriber_id' => $tenant->id,
                'plan_id' => $plan->id,
                'name' => 'principal',
                'slug' => 'main',
                'description' => 'Suscripción principal del tenant',
                'status' => TenantSubscription::STATUS_ACTIVE,
                'billing_cycle' => $billingCycle,
                'payment_status' => 'pending',
            ]);

            $subscription->setBillingPeriodFromCycle();
            $subscription->save();

            $tenant->update([
                'plan_id' => $plan->id,
                'subscribed_at' => now(),
                'is_active' => true,
            ]);

            if (filled($gateway)) {
                $this->createRenewalPayment($subscription, (string) $gateway);
            }

            $this->alertService->notify(
                type: 'subscription_created',
                title: 'Suscripción creada',
                message: 'Se creó la suscripción del tenant y quedó activa.',
                subscription: $subscription,
                level: 'success',
                context: [
                    'plan_id' => $plan->id,
                    'billing_cycle' => $billingCycle,
                ],
            );

            return $subscription->refresh();
        });
    }

    public function changePlan(
        TenantSubscription $subscription,
        Plan $newPlan,
        string $newBillingCycle,
        bool $prorate = true,
        ?string $gateway = null,
    ): TenantSubscription {
        return DB::transaction(function () use ($subscription, $newPlan, $newBillingCycle, $prorate, $gateway): TenantSubscription {
            $credit = 0.0;
            if ($prorate) {
                $credit = $this->calculateProratedCredit($subscription);
            }

            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $newBillingCycle,
                'status' => TenantSubscription::STATUS_ACTIVE,
                'payment_status' => 'pending',
                'grace_ends_at' => null,
                'suspended_at' => null,
            ]);

            $subscription->setBillingPeriodFromCycle();
            $subscription->save();

            $subscription->tenant()->update([
                'plan_id' => $newPlan->id,
                'is_active' => true,
            ]);

            if (filled($gateway)) {
                $metadata = [
                    'billing_type' => 'proration_change',
                    'credit_applied' => $credit,
                    'subscription_id' => $subscription->id,
                ];

                $amount = max($newPlan->getPriceForCycle($newBillingCycle) - $credit, 0);
                if ($amount > 0) {
                    $this->createRenewalPayment($subscription, (string) $gateway, $amount, $metadata);
                } else {
                    $subscription->markAsPaid();
                }
            }

            $this->alertService->notify(
                type: 'subscription_plan_changed',
                title: 'Cambio de plan aplicado',
                message: 'La suscripción cambió de plan/ciclo con prorrateo inmediato.',
                subscription: $subscription,
                level: 'info',
                context: [
                    'new_plan_id' => $newPlan->id,
                    'new_billing_cycle' => $newBillingCycle,
                    'credit_applied' => $credit,
                ],
            );

            return $subscription->refresh();
        });
    }

    public function createRenewalPayment(
        TenantSubscription $subscription,
        string $gateway,
        ?float $amount = null,
        array $metadata = [],
    ): Payment {
        $plan = $subscription->plan;
        if (! $plan instanceof Plan) {
            throw new RuntimeException('La suscripción no tiene plan válido.');
        }

        $chargeAmount = $amount ?? $plan->getPriceForCycle($subscription->billing_cycle);

        return $this->paymentService->initiate(
            payable: $subscription,
            gateway: $gateway,
            amount: $chargeAmount,
            options: [
                'currency' => (string) ($plan->currency ?? 'USD'),
                'customer_name' => $subscription->tenant?->company_name,
                'customer_email' => null,
                'metadata' => array_merge($metadata, [
                    'billing_type' => $metadata['billing_type'] ?? 'subscription_renewal',
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                ]),
            ],
        );
    }

    public function handlePaymentStatusFromGateway(Payment $payment): void
    {
        if ($payment->paymentable_type !== TenantSubscription::class) {
            return;
        }

        /** @var TenantSubscription|null $subscription */
        $subscription = TenantSubscription::query()->find($payment->paymentable_id);
        if ($subscription === null) {
            return;
        }

        if ($payment->status === Payment::STATUS_COMPLETED) {
            $subscription->markAsPaid((string) ($payment->payment_method ?? 'card'));
            $subscription->setBillingPeriodFromCycle();
            $subscription->next_billing_at = $subscription->ends_at;
            $subscription->save();

            $this->alertService->notify(
                type: 'subscription_payment_completed',
                title: 'Pago de suscripción confirmado',
                message: 'La suscripción fue renovada correctamente.',
                subscription: $subscription,
                level: 'success',
                context: [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference_number,
                ],
            );

            return;
        }

        if ($payment->status === Payment::STATUS_FAILED) {
            $subscription->markPastDue(
                graceDays: (int) ($subscription->plan?->grace_period ?? 7),
            );

            $nextRetryAt = $this->nextRetryDate($subscription->retry_count + 1);
            $subscription->update([
                'retry_count' => $subscription->retry_count + 1,
                'last_retry_at' => now(),
                'next_retry_at' => $nextRetryAt,
            ]);

            $this->alertService->notify(
                type: 'subscription_payment_failed',
                title: 'Fallo en cobro de suscripción',
                message: 'Se inició periodo de gracia para el tenant.',
                subscription: $subscription,
                level: 'warning',
                context: [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference_number,
                    'grace_ends_at' => optional($subscription->fresh()->grace_ends_at)?->toIso8601String(),
                ],
            );
        }
    }

    public function processDueRenewals(Carbon $asOf, string $gateway): int
    {
        $processed = 0;

        TenantSubscription::query()
            ->with(['plan', 'tenant'])
            ->where('status', TenantSubscription::STATUS_ACTIVE)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', $asOf)
            ->chunkById(100, function ($subscriptions) use (&$processed, $gateway): void {
                foreach ($subscriptions as $subscription) {
                    $this->createRenewalPayment($subscription, $gateway);
                    $processed++;
                }
            });

        TenantSubscription::query()
            ->with(['plan', 'tenant'])
            ->where('status', TenantSubscription::STATUS_PAST_DUE)
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', $asOf)
            ->where(function ($query) use ($asOf): void {
                $query->whereNull('grace_ends_at')
                    ->orWhere('grace_ends_at', '>=', $asOf);
            })
            ->chunkById(100, function ($subscriptions) use (&$processed, $gateway): void {
                foreach ($subscriptions as $subscription) {
                    $this->createRenewalPayment($subscription, $gateway, null, [
                        'billing_type' => 'subscription_retry',
                        'retry_count' => $subscription->retry_count,
                    ]);
                    $processed++;
                }
            });

        TenantSubscription::query()
            ->where('status', TenantSubscription::STATUS_PAST_DUE)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', $asOf)
            ->chunkById(100, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    $subscription->suspend();
                    $this->alertService->notify(
                        type: 'subscription_suspended',
                        title: 'Tenant suspendido por mora',
                        message: 'Finalizó el periodo de gracia sin pago.',
                        subscription: $subscription,
                        level: 'danger',
                        context: [
                            'grace_ends_at' => optional($subscription->grace_ends_at)?->toIso8601String(),
                        ],
                    );
                }
            });

        return $processed;
    }

    private function nextRetryDate(int $attempt): ?CarbonInterface
    {
        $index = $attempt - 1;
        if (! isset(self::RETRY_DAYS[$index])) {
            return null;
        }

        return now()->addDays(self::RETRY_DAYS[$index]);
    }

    private function calculateProratedCredit(TenantSubscription $subscription): float
    {
        if ($subscription->ends_at === null || $subscription->starts_at === null || ! $subscription->plan instanceof Plan) {
            return 0.0;
        }

        $totalSeconds = max($subscription->starts_at->diffInSeconds($subscription->ends_at), 1);
        $remainingSeconds = max(now()->diffInSeconds($subscription->ends_at, false), 0);
        $remainingRatio = $remainingSeconds / $totalSeconds;

        return round($subscription->plan->getPriceForCycle($subscription->billing_cycle) * $remainingRatio, 2);
    }
}
