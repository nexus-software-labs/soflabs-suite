<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use Database\Factories\Inventory\SupplierFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_suppliers';

    protected $fillable = [
        'tenant_id',
        'name',
        'tax_id',
        'supplier_type',
        'country_code',
        'payment_terms',
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
     * @return HasMany<SupplierContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    /**
     * @return HasMany<SupplierProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }
}
