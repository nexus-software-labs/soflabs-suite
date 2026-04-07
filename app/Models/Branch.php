<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTenantScope;
use App\Models\Core\Country;
use App\Models\Core\Customer;
use App\Models\Printing\PrintOrder;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasTenantScope, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'country_id',
        'name',
        'code',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'is_main',
        'is_active',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
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
     * País (catálogo central) para promociones por país/región.
     */
    public function countryModel(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'branch_id');
    }

    /**
     * @return HasMany<PrintOrder, $this>
     */
    public function printOrders(): HasMany
    {
        return $this->hasMany(PrintOrder::class, 'branch_id');
    }
}
