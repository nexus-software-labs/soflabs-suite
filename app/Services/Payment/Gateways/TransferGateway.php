<?php

namespace App\Services\Payment\Gateways;

use App\Models\Core\Payment;
use App\Models\PreAlertOrder;
use App\Models\PrintOrder;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Gateway de pago por transferencia bancaria
 * El usuario sube comprobante, admin confirma manualmente
 */
class TransferGateway implements PaymentGatewayInterface
{
    public function initiate(Payment $payment, array $options = []): Payment
    {
        $payable = $payment->paymentable;
        if (! $payable) {
            throw new \Exception('No se puede iniciar el pago: modelo relacionado no encontrado');
        }

        // Guardar referencia y notas si se proporcionan
        $updateData = [
            'payment_method' => 'transfer',
            'transfer_reference' => $options['transfer_reference'] ?? null,
            'transfer_notes' => $options['transfer_notes'] ?? null,
        ];

        // Subir comprobante de transferencia si existe
        $file = $options['transfer_proof'] ?? null;
        if ($file instanceof UploadedFile) {
            $payment->addMedia($file)
                ->usingFileName('transfer_proof_'.$payment->id.'_'.time().'.'.$file->getClientOriginalExtension())
                ->toMediaCollection('transfer_proof');
        }

        // URL de redirección: página de "pago pendiente"
        $updateData['redirect_url'] = $this->getPendingRedirectUrl($payable, $payment);

        $payment->update($updateData);

        Log::info('Pago por transferencia creado', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference_number,
        ]);

        return $payment;
    }

    public function handleCallback(array $data): Payment
    {
        throw new \Exception('Los pagos por transferencia no usan callback');
    }

    public function verifySignature(array $data): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'transfer';
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
