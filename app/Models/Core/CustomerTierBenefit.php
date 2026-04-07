<?php

namespace App\Models\Core;

use App\Models\Branch;
use App\Models\User;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerTierBenefit extends Model
{
    use RecordSignature, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'value' => 'array', // JSON
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        // Sincronizar el campo 'type' con 'discount_type' para compatibilidad
        static::saving(function ($benefit) {
            if ($benefit->discount_type && ! $benefit->type) {
                // Mapear discount_type a type para compatibilidad
                $benefit->type = match ($benefit->discount_type) {
                    'percentage' => 'discount',
                    'fixed_amount' => 'discount',
                    'fixed_rate' => 'discount',
                    default => 'discount',
                };
            }
        });
    }

    /**
     * Relación con categoría de cliente
     */
    public function customerTier(): BelongsTo
    {
        return $this->belongsTo(CustomerTier::class);
    }

    /**
     * Relación con región
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Relación con país
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Relación con tiendas (many-to-many)
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'customer_tier_benefit_branches')
            ->withTimestamps();
    }

    /**
     * Relación con usuario creador
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con usuario actualizador
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para beneficios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por prioridad
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    /**
     * Verificar si el beneficio aplica a una tienda específica
     */
    public function appliesToBranch(string $branchId): bool
    {
        if ($this->scope_type === 'all') {
            return true;
        }

        if ($this->scope_type === 'branches') {
            return $this->branches()->where('branches.id', $branchId)->exists();
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
     * Verificar si el beneficio aplica a un servicio específico
     */
    public function appliesToService(string $serviceType): bool
    {
        return $this->service_type === 'both' || $this->service_type === $serviceType;
    }

    /**
     * Calcular el descuento según el tipo
     */
    public function calculateDiscount(float $amount): float
    {
        $discount = match ($this->discount_type) {
            'percentage' => ($amount * $this->discount_value / 100),
            'fixed_amount' => min($this->discount_value, $amount),
            'fixed_rate' => 0, // La tarifa fija se calcula diferente
            default => 0,
        };

        if ($this->discount_type === 'percentage' && $this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return round($discount, 2);
    }

    /**
     * Obtener etiqueta del descuento
     */
    public function getDiscountLabel(): string
    {
        if ($this->discount_type === 'percentage') {
            return "-{$this->discount_value}%";
        }

        if ($this->discount_type === 'fixed_rate') {
            return '$'.number_format($this->discount_value, 2).'/lb';
        }

        return '-$'.number_format($this->discount_value, 2);
    }
}
