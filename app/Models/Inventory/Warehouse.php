<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\Inventory\WarehouseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    /** @use HasFactory<WarehouseFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_warehouses';

    protected $fillable = [
        'tenant_id',
        'name',
        'warehouse_type',
        'location',
        'responsible_user_id',
        'status',
    ];

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return HasMany<WarehouseZone, $this>
     */
    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class, 'warehouse_id');
    }

    /**
     * @return HasMany<Stock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'warehouse_id');
    }

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class, 'warehouse_id');
    }

    /**
     * @return HasMany<Adjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class, 'warehouse_id');
    }

    /**
     * @return HasMany<OutboundRequest, $this>
     */
    public function outboundRequests(): HasMany
    {
        return $this->hasMany(OutboundRequest::class, 'warehouse_id');
    }
}
