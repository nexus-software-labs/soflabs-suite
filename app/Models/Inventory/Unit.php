<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use Database\Factories\Inventory\UnitFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_units';

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
        'unit_type',
        'is_system',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * @return HasMany<UnitConversion, $this>
     */
    public function outgoingConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    /**
     * @return HasMany<UnitConversion, $this>
     */
    public function incomingConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }
}
