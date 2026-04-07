<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\Concerns\HasTenantScope;
use App\Models\User;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasTenantScope, InteractsWithMedia, RecordSignature, SoftDeletes;

    protected $table = 'payments';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal_pre_alerta' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Payment $payment): void {
            if (filled($payment->tenant_id) || ! $payment->paymentable_type || ! $payment->paymentable_id) {
                return;
            }

            $class = $payment->paymentable_type;
            if (! is_string($class) || ! class_exists($class)) {
                return;
            }

            $payable = $class::withoutGlobalScopes()->find($payment->paymentable_id);
            if ($payable !== null && array_key_exists('tenant_id', $payable->getAttributes()) && filled($payable->getAttribute('tenant_id'))) {
                $payment->tenant_id = $payable->getAttribute('tenant_id');
            }
        });
    }

    /**
     * Estados disponibles
     */
    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_REFUNDED = 'refunded';

    /**
     * Gateways disponibles
     */
    const GATEWAY_CYBERSOURCE = 'cybersource';

    const GATEWAY_CASH = 'cash';

    const GATEWAY_TRANSFER = 'transfer';

    /**
     * Configuración de colecciones de media
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('transfer_proof')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'])
            ->useDisk('public');
    }

    /**
     * RELACIONES
     */

    /**
     * Relación polimórfica: el pago pertenece a cualquier modelo (PrintOrder, PreAlertOrder, etc.)
     */
    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario que realizó el pago
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detalles del pago con costos desglosados
     */
    public function detail(): HasOne
    {
        return $this->hasOne(PaymentDetail::class);
    }

    /**
     * SCOPES
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * MÉTODOS DE ESTADO
     */

    /**
     * Marcar como completado.
     * Notifica al modelo pagado (PreAlertOrder, PrintOrder, etc.) para que ejecute
     * su lógica post-pago (ej. crear shipment Boxful si es casillero).
     */
    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);

        $payable = $this->paymentable;
        if ($payable && method_exists($payable, 'markAsPaid')) {
            $payable->markAsPaid($this->payment_method ?? 'card');
        }
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed(?string $reasonCode = null, ?string $reasonMessage = null, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'reason_code' => $reasonCode,
            'reason_message' => $reasonMessage,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Marcar como procesando
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);
    }

    /**
     * Marcar como cancelado
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * ACCESSORS
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsProcessingAttribute(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PROCESSING => 'Procesando',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_FAILED => 'Fallido',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_REFUNDED => 'Reembolsado',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_REFUNDED => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Generar número de referencia único
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'PAY-'.strtoupper(uniqid());
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }
}
