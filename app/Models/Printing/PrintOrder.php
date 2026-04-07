<?php

declare(strict_types=1);

namespace App\Models\Printing;

use App\Models\Branch;
use App\Models\Concerns\HasTenantScope;
use App\Models\Core\Customer;
use App\Models\Core\PickupLocation;
use App\Models\Core\Promotion;
use App\Models\User;
use App\Traits\Payable;
use App\Traits\RecordSignature;
use Database\Factories\PrintOrderFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PrintOrder extends Model implements HasMedia
{
    use HasTenantScope, InteractsWithMedia, Payable, RecordSignature, SoftDeletes;

    protected static function newFactory(): PrintOrderFactory
    {
        return static::applyTenantContextToFactory(PrintOrderFactory::new());
    }

    protected $table = 'print_orders';

    protected $guarded = ['id'];

    protected $casts = [
        'binding' => 'boolean',
        'double_sided' => 'boolean',
        'is_plan' => 'boolean',
        'copies' => 'integer',
        'pages_count' => 'integer',
        'delivery_cost' => 'decimal:2',
        'price_per_page' => 'decimal:2',
        'binding_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',

        'discount' => 'decimal:2',
        'delivery_discount' => 'decimal:2',

        'paid_at' => 'datetime',
        'printed_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (PrintOrder $order): void {
            if (filled($order->tenant_id)) {
                return;
            }

            if ($order->branch_id) {
                $branch = Branch::withoutGlobalScopes()->find($order->branch_id);
                if ($branch !== null && filled($branch->tenant_id)) {
                    $order->tenant_id = $branch->tenant_id;

                    return;
                }
            }

            if (function_exists('tenancy') && tenancy()->initialized && tenant()) {
                $order->tenant_id = tenant('id');
            }
        });
    }

    /**
     * Configuración de Spatie Media Library
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('print-files')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/jpg',
            ])
            ->useDisk('public');
    }

    /**
     * RELACIONES
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Cliente asociado a través del usuario (user_id -> users -> customers).
     * PrintOrder no tiene customer_id; los datos de contacto se guardan en customer_name/email/phone.
     * Esta relación sirve como fallback cuando el usuario tiene un Customer (ej. pago, notificaciones).
     */
    public function customer(): HasOneThrough
    {
        return $this->hasOneThrough(Customer::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(PrintOrderHistory::class);
    }

    /**
     * 🎯 RELACIÓN CON PROMOCIÓN
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * SCOPES
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInQueue($query)
    {
        return $query->where('status', 'in_queue');
    }

    public function scopePrinting($query)
    {
        return $query->where('status', 'printing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * MÉTODOS DE ESTADO
     */
    public function updateStatus($newStatus, $comment = null, $userId = null)
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);

        // Registrar en historial
        $this->history()->create([
            'status' => $newStatus,
            'comment' => $comment,
            'created_by' => $userId ?? auth()->id(),
        ]);

        // Actualizar timestamps según el estado
        switch ($newStatus) {
            case 'printing':
                $this->update(['printed_at' => now()]);
                break;
            case 'ready':
                $this->update(['ready_at' => now()]);
                break;
            case 'delivered':
                $this->update(['delivered_at' => now()]);
                break;
        }
    }

    public function markAsPaid($paymentMethod = 'cash')
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'paid_at' => now(),
        ]);

        $this->history()->create([
            'status' => 'payment_pending',
            'comment' => 'Pago confirmado',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * ACCESSORS
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '📤 Pendiente',
            'processing' => '🖨️ En Proceso',
            'ready' => '✅ Listo',
            'delivered' => '✓ Entregado',
            'cancelled' => '❌ Cancelado',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'ready' => 'success',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_status === 'paid';
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * 🎯 ACCESSORS PARA PROMOCIONES
     */
    public function getTotalDiscountAttribute(): float
    {
        return ($this->discount ?? 0) + ($this->delivery_discount ?? 0);
    }

    public function getHasPromotionAttribute(): bool
    {
        return ! is_null($this->promotion_id);
    }

    public function getSubtotalBeforeDiscountAttribute(): float
    {
        return $this->subtotal + ($this->discount ?? 0);
    }

    public function getDeliveryCostBeforeDiscountAttribute(): float
    {
        return $this->delivery_cost + ($this->delivery_discount ?? 0);
    }

    /**
     * 🎯 MÉTODO PARA RECALCULAR TOTAL
     */
    public function calculateTotal(): float
    {
        $this->total = $this->subtotal + $this->delivery_cost + ($this->tax ?? 0);

        return $this->total;
    }
}
