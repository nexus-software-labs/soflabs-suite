<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\HasTenantScope;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\Inventory\IntakeDocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntakeDocument extends Model
{
    /** @use HasFactory<IntakeDocumentFactory> */
    use HasTenantScope, HasUlids;

    protected $table = 'inventory_intake_documents';

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'warehouse_id',
        'document_number',
        'document_date',
        'currency_code',
        'subtotal',
        'tax',
        'total',
        'status',
        'origin',
        'source_file_path',
        'ai_confidence',
        'warnings',
        'raw_extraction',
        'created_by',
        'approved_by',
        'processed_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'subtotal' => 'decimal:4',
            'tax' => 'decimal:4',
            'total' => 'decimal:4',
            'ai_confidence' => 'decimal:4',
            'warnings' => 'array',
            'raw_extraction' => 'array',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
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
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return HasMany<IntakeDocumentLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(IntakeDocumentLine::class, 'intake_document_id');
    }
}
