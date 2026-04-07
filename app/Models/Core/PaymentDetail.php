<?php

declare(strict_types=1);

namespace App\Models\Core;

use App\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDetail extends Model
{
    use HasTenantScope;

    protected $table = 'payment_details';

    protected $guarded = ['id'];

    protected $casts = [
        'flete' => 'decimal:2',
        'garantia_entrega' => 'decimal:2',
        'iva_cif' => 'decimal:2',
        'dai' => 'decimal:2',
        'total_impuestos' => 'decimal:2',
        'gestion_aduanal' => 'decimal:2',
        'manejo_terceros' => 'decimal:2',
        'weight_lbs' => 'decimal:2',
        'value_declared' => 'decimal:2',
        'valor_por_libra' => 'decimal:2',
        'dai_percentage' => 'decimal:2',
        'aplica_dai' => 'boolean',
        'umbral_dai' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PaymentDetail $detail): void {
            if (filled($detail->tenant_id) || ! $detail->payment_id) {
                return;
            }

            $payment = Payment::withoutGlobalScopes()->find($detail->payment_id);
            if ($payment !== null && filled($payment->tenant_id)) {
                $detail->tenant_id = $payment->tenant_id;
            }
        });
    }

    /**
     * RELACIONES
     */

    /**
     * Relación con el pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
