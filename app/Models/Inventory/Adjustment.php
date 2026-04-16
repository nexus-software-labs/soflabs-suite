<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\Inventory\AdjustmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adjustment extends Model
{
    /** @use HasFactory<AdjustmentFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'tenant_id',
        'movement_id',
        'product_id',
        'warehouse_id',
        'adjustment_type',
        'difference_quantity',
        'reason',
        'evidence_path',
        'notes',
        'performed_by',
        'adjusted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difference_quantity' => 'decimal:4',
            'adjusted_at' => 'datetime',
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
     * @return BelongsTo<Movement, $this>
     */
    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'movement_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
