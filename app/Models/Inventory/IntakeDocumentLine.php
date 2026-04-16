<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use Database\Factories\Inventory\IntakeDocumentLineFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntakeDocumentLine extends Model
{
    /** @use HasFactory<IntakeDocumentLineFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_intake_document_lines';

    protected $fillable = [
        'tenant_id',
        'intake_document_id',
        'line_number',
        'product_id',
        'description_original',
        'quantity',
        'unit_id',
        'unit_price',
        'subtotal',
        'linked_manually',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'subtotal' => 'decimal:4',
            'linked_manually' => 'boolean',
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
     * @return BelongsTo<IntakeDocument, $this>
     */
    public function intakeDocument(): BelongsTo
    {
        return $this->belongsTo(IntakeDocument::class, 'intake_document_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
