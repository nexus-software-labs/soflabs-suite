<?php

declare(strict_types=1);

namespace App\Models\Subscriptions;

use App\Models\Plan;
use App\Models\Tenant;
use App\Traits\Payable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravelcm\Subscriptions\Models\Subscription;
use Laravelcm\Subscriptions\Services\Period;

class TenantSubscription extends Subscription
{
    use Payable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'tenant_id',
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'slug',
        'name',
        'description',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'status',
        'billing_cycle',
        'next_billing_at',
        'grace_ends_at',
        'suspended_at',
        'gateway_customer_ref',
        'gateway_subscription_ref',
        'payment_status',
        'retry_count',
        'last_retry_at',
        'next_retry_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            if (blank($subscription->tenant_id) && $subscription->subscriber_type === Tenant::class && filled($subscription->subscriber_id)) {
                $subscription->tenant_id = (string) $subscription->subscriber_id;
            }

            if (blank($subscription->status)) {
                $subscription->status = self::STATUS_ACTIVE;
            }

            if (blank($subscription->payment_status)) {
                $subscription->payment_status = 'pending';
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function markAsPaid(string $paymentMethod = 'card'): void
    {
        $this->update([
            'payment_status' => 'paid',
            'status' => self::STATUS_ACTIVE,
            'grace_ends_at' => null,
            'suspended_at' => null,
            'retry_count' => 0,
            'last_retry_at' => null,
            'next_retry_at' => null,
        ]);

        if ($this->tenant !== null) {
            $this->tenant->update([
                'is_active' => true,
                'subscribed_at' => now(),
            ]);
        }
    }

    public function setBillingPeriodFromCycle(?Carbon $start = null): void
    {
        $startDate = $start ?? Carbon::now();
        $interval = $this->billing_cycle === 'yearly' ? 'year' : 'month';

        $period = new Period(
            interval: $interval,
            count: 1,
            start: $startDate,
        );

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();
        $this->next_billing_at = $period->getEndDate();
    }

    public function markPastDue(int $graceDays = 7): void
    {
        $this->update([
            'status' => self::STATUS_PAST_DUE,
            'payment_status' => 'failed',
            'grace_ends_at' => now()->addDays($graceDays),
        ]);
    }

    public function suspend(): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);

        if ($this->tenant !== null) {
            $this->tenant->update(['is_active' => false]);
        }
    }
}
