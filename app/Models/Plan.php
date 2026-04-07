<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelcm\Subscriptions\Interval;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\Subscription;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'price',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'sort_order',
        'is_active',
        'modules',
        'limits',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'limits' => 'array',
            'features' => 'array',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'price' => 'decimal:2',
            'signup_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.feature', Feature::class), 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription', Subscription::class), 'plan_id');
    }

    public function getPriceForCycle(string $billingCycle): float
    {
        return $billingCycle === 'yearly'
            ? (float) ($this->price_yearly ?? 0)
            : (float) ($this->price_monthly ?? 0);
    }

    public function billingIntervalForCycle(string $billingCycle): string
    {
        return $billingCycle === 'yearly'
            ? Interval::YEAR->value
            : Interval::MONTH->value;
    }

    public function billingPeriodForCycle(string $billingCycle): int
    {
        return 1;
    }

    public function isFree(): bool
    {
        return (float) ($this->price ?? 0) <= 0;
    }

    public function hasTrial(): bool
    {
        return (int) ($this->trial_period ?? 0) > 0 && filled($this->trial_interval);
    }

    public function hasGrace(): bool
    {
        return (int) ($this->grace_period ?? 0) > 0 && filled($this->grace_interval);
    }
}
