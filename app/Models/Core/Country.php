<?php

namespace App\Models\Core;

use App\Models\Branch;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, RecordSignature, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'shipping_pound_value' => 'decimal:2',
        'customs_management' => 'decimal:2',
        'third_party_handling' => 'decimal:2',
        'delivery_guarantee_percentage' => 'decimal:4',
        'iva_cif_percentage' => 'decimal:4',
        'dai_percentage' => 'decimal:4',
        'dai_threshold' => 'decimal:2',
    ];

    // Relaciones
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Sucursales enlazadas por {@see Branch::$country_id}.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'country_id');
    }

    public function activeBranches(): HasMany
    {
        return $this->hasMany(Branch::class, 'country_id')->where('is_active', true);
    }

    public function getBranchesCountAttribute(): int
    {
        return $this->branches()->count();
    }

    /**
     * Obtener configuración de cálculo por código de país (2 o 3 caracteres)
     */
    public static function getShippingConfigByCode(string $countryCode): ?self
    {
        return static::where('code', $countryCode)->first();
    }

    /**
     * Obtener solo la configuración de cálculo
     */
    public function getShippingCalculationConfig(): array
    {
        return [
            'shipping_pound_value' => $this->shipping_pound_value ?? 4.99,
            'customs_management' => $this->customs_management ?? 4.99,
            'third_party_handling' => $this->third_party_handling ?? 2.74,
            'delivery_guarantee_percentage' => $this->delivery_guarantee_percentage ?? 0.01,
            'iva_cif_percentage' => $this->iva_cif_percentage ?? 0.145,
            'dai_percentage' => $this->dai_percentage,
            'dai_threshold' => $this->dai_threshold ?? 300.00,
        ];
    }
}
