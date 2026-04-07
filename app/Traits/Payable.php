<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Core\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Modelos que reciben pagos vía relación polimórfica {@see Payment}.
 */
trait Payable
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function isPaid(): bool
    {
        return $this->getAttribute('payment_status') === 'paid';
    }
}
