<?php

declare(strict_types=1);

namespace App\Models\Printing;

use App\Models\Concerns\HasTenantScope;
use App\Models\User;
use Database\Factories\PrintOrderHistoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintOrderHistory extends Model
{
    use HasTenantScope;

    protected static function newFactory(): PrintOrderHistoryFactory
    {
        return static::applyTenantContextToFactory(PrintOrderHistoryFactory::new());
    }

    protected $table = 'print_order_history';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (PrintOrderHistory $history): void {
            if (filled($history->tenant_id) || ! $history->print_order_id) {
                return;
            }

            $order = PrintOrder::withoutGlobalScopes()->find($history->print_order_id);
            if ($order !== null && filled($order->tenant_id)) {
                $history->tenant_id = $order->tenant_id;
            }
        });
    }

    /**
     * Relación: Pertenece a un pedido
     */
    public function printOrder(): BelongsTo
    {
        return $this->belongsTo(PrintOrder::class);
    }

    /**
     * Relación: Usuario que hizo el cambio
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor: Etiqueta del estado en español
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
