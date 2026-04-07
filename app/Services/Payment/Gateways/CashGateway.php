<?php

namespace App\Services\Payment\Gateways;

use App\Models\Core\Payment;
use App\Models\PreAlertOrder;
use App\Models\PrintOrder;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Gateway de pago contra entrega (efectivo)
 * El pago se completa cuando el admin confirma que recibió el efectivo
 */
class CashGateway implements PaymentGatewayInterface
{
    public function initiate(Payment $payment, array $options = []): Payment
    {
        $payable = $payment->paymentable;
        if (! $payable) {
            throw new \Exception('No se puede iniciar el pago: modelo relacionado no encontrado');
        }

        // URL de redirección: página de "pago pendiente - pagar al recoger/recibir"
        $redirectUrl = $this->getPendingRedirectUrl($payable, $payment);

        $payment->update([
            'payment_method' => 'cash',
            'redirect_url' => $redirectUrl,
        ]);

        Log::info('Pago contra entrega creado', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference_number,
        ]);

        return $payment;
    }

    public function handleCallback(array $data): Payment
    {
        throw new \Exception('Los pagos contra entrega no usan callback');
    }

    public function verifySignature(array $data): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'cash';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    protected function getPendingRedirectUrl($payable, Payment $payment): string
    {
        $paymentableType = get_class($payable);

        return match ($paymentableType) {
            PreAlertOrder::class => route('pre-alerts.payment-success', $payable->id).'?pending=1',
            PrintOrder::class => route('print-orders.payment-success', $payable->id).'?pending=1',
            default => url('/'),
        };
    }
}
