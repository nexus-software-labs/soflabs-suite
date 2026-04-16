<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use Database\Factories\Inventory\OutboundRequestLineFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundRequestLine extends Model
{
    /** @use HasFactory<OutboundRequestLineFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_outbound_request_lines';

    protected $fillable = [
        'tenant_id',
        'outbound_request_id',
        'line_number',
        'product_id',
        'requested_quantity',
        'reserved_quantity',
        'dispatched_quantity',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:4',
            'reserved_quantity' => 'decimal:4',
            'dispatched_quantity' => 'decimal:4',
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
     * @return BelongsTo<OutboundRequest, $this>
     */
    public function outboundRequest(): BelongsTo
    {
        return $this->belongsTo(OutboundRequest::class, 'outbound_request_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
