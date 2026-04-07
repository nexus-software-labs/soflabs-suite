<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Core\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {}
}
