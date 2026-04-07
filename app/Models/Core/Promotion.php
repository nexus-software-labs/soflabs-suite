<?php

// app/Models/Promotion.php

namespace App\Models\Core;

use App\Models\Branch;
use App\Models\Concerns\HasTenantScope;
use App\Models\User;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Promotion extends Model
{
    use HasTenantScope, RecordSignature, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($promotion) {
            if ($promotion->application_type === 'coupon' && ! $promotion->coupon_code) {
                $promotion->coupon_code = strtoupper(Str::random(8));
            }
        });
    }

    // Relaciones
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'promotion_branches')
            ->withTimestamps();
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'promotion_customers')
            ->withTimestamps();
    }

    public function customerTier(): BelongsTo
    {
        return $this->belongsTo(CustomerTier::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PromotionApplication::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }

    public function scopeForService($query, string $serviceType)
    {
        return $query->where(function ($q) use ($serviceType) {
            $q->where('service_type', 'both')
                ->orWhere('service_type', $serviceType);
        });
    }

    // Helpers
    public function appliesToBranch(string $branchId): bool
    {
        if ($this->scope_type === 'all') {
            return true;
        }

        if ($this->scope_type === 'branches') {
            return $this->branches()->where('branches.id', $branchId)->exists();
        }

        if ($this->scope_type === 'customers') {
            return false;
        }

        $branch = Branch::withoutGlobalScopes()
            ->with(['countryModel.region'])
            ->find($branchId);

        if (! $branch) {
            return false;
        }

        if ($this->scope_type === 'country') {
            if ($branch->country_id !== null) {
                return (int) $branch->country_id === (int) $this->country_id;
            }

            $country = $branch->country
                ? Country::where('code', $branch->country)->first()
                : null;

            return $country && (int) $country->id === (int) $this->country_id;
        }

        if ($this->scope_type === 'region') {
            $regionId = $branch->countryModel?->region_id;

            return $regionId !== null && (int) $regionId === (int) $this->region_id;
        }

        return false;
    }

    /**
     * Verificar si la promoción aplica a un customer específico
     */
    public function appliesToCustomer(int $customerId): bool
    {
        // Si la promoción es específica para una categoría de cliente
        if ($this->customer_tier_id) {
            $customer = Customer::find($customerId);
            if (! $customer || ! $customer->customer_tier_id) {
                return false;
            }

            return $customer->customer_tier_id === $this->customer_tier_id;
        }

        // Si es scope de customers específicos
        if ($this->scope_type === 'customers') {
            return $this->customers()->where('customer_id', $customerId)->exists();
        }

        $customer = Customer::find($customerId);
        if (! $customer || ! $customer->branch_id) {
            return false;
        }

        return $this->appliesToBranch((string) $customer->branch_id);
    }

    public function meetsMinimumAmount(?float $orderAmount): bool
    {
        if (! $this->min_order_amount) {
            return true;
        }

        return $orderAmount >= $this->min_order_amount;
    }

    public function calculateDiscount(float $amount): float
    {
        $discount = match ($this->discount_type) {
            'free_delivery' => $amount, // 100% del delivery
            'percentage' => ($amount * $this->discount_value / 100),
            'fixed_amount' => min($this->discount_value, $amount),
            default => 0,
        };

        if ($this->discount_type === 'percentage' && $this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return round($discount, 2);
    }

    public function getDiscountLabel(): string
    {
        if ($this->discount_type === 'free_delivery') {
            return 'Envío Gratis';
        }

        if ($this->discount_type === 'percentage') {
            return "-{$this->discount_value}%";
        }

        return '-$'.number_format($this->discount_value, 2);
    }
}
