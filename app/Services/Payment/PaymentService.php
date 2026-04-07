<?php

namespace App\Services\Payment;

use App\Events\PaymentCompleted;
use App\Events\PaymentCreated;
use App\Events\PaymentFailed;
use App\Models\Core\Payment;
use App\Models\Core\PaymentDetail;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Servicio principal para manejar pagos
 *
 * Este servicio actúa como facade centralizado para todos los pagos
 * en el sistema, independientemente del gateway utilizado.
 */
class PaymentService
{
    protected PaymentGatewayFactory $factory;

    public function __construct(PaymentGatewayFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Iniciar un nuevo pago
     *
     * @param  Model  $payable  El modelo que necesita ser pagado (PrintOrder, PreAlertOrder, etc.)
     * @param  string  $gateway  Nombre del gateway a usar (cybersource, cash, etc.)
     * @param  float  $amount  Monto a pagar
     * @param  array  $options  Opciones adicionales (customer data, return_url, etc.)
     *
     * @throws \Exception Si el gateway no está disponible o hay un error
     */
    public function initiate(
        Model $payable,
        string $gateway,
        float $amount,
        array $options = []
    ): Payment {
        // Validar que el gateway esté disponible
        if (! PaymentGatewayFactory::isAvailable($gateway)) {
            throw new \Exception("Gateway '{$gateway}' no está disponible");
        }

        // Validar que el modelo tenga el trait Payable
        if (! in_array(Payable::class, class_uses_recursive($payable))) {
            throw new \Exception('El modelo '.get_class($payable).' debe usar el trait Payable');
        }

        // Crear el registro de pago
        $payment = Payment::create([
            'paymentable_type' => get_class($payable),
            'paymentable_id' => $payable->id,
            'gateway' => $gateway,
            'amount' => $amount,
            'currency' => $options['currency'] ?? 'USD',
            'reference_number' => Payment::generateReferenceNumber(),
            'status' => Payment::STATUS_PENDING,
            'user_id' => $options['user_id'] ?? auth()->id(),
            'customer_name' => $options['customer_name'] ?? null,
            'customer_email' => $options['customer_email'] ?? null,
            'customer_phone' => $options['customer_phone'] ?? null,
            'return_url' => $options['return_url'] ?? null,
            'cancel_url' => $options['cancel_url'] ?? null,
            'metadata' => $options['metadata'] ?? [],
            'subtotal_pre_alerta' => $options['subtotal_pre_alerta'] ?? null,
            'costo_envio' => $options['costo_envio'] ?? null,
            'total' => $options['total'] ?? $amount,
        ]);

        // Crear el detalle del pago con los costos desglosados (si están disponibles)
        if (! empty($options['calculation'])) {
            $calculation = $options['calculation'];
            PaymentDetail::create([
                'payment_id' => $payment->id,
                'flete' => $calculation['flete'] ?? 0,
                'garantia_entrega' => $calculation['garantia_entrega'] ?? 0,
                'iva_cif' => $calculation['iva_cif'] ?? 0,
                'dai' => $calculation['dai'] ?? 0,
                'total_impuestos' => $calculation['total_impuestos'] ?? 0,
                'gestion_aduanal' => $calculation['gestion_aduanal'] ?? 0,
                'manejo_terceros' => $calculation['manejo_terceros'] ?? 0,
                'weight_lbs' => $calculation['weight_lbs'] ?? null,
                'value_declared' => $calculation['value_declared'] ?? null,
                'valor_por_libra' => $calculation['valor_por_libra'] ?? null,
                'dai_percentage' => $calculation['dai_percentage'] ?? null,
                'aplica_dai' => filter_var($calculation['aplica_dai'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'umbral_dai' => $calculation['umbral_dai'] ?? null,
            ]);
        }

        // Obtener el gateway y procesar el pago
        $gatewayInstance = $this->factory::make($gateway);

        try {
            $payment = $gatewayInstance->initiate($payment, $options);

            // Disparar evento
            event(new PaymentCreated($payment));

            Log::info('Pago iniciado', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference_number,
                'gateway' => $gateway,
                'amount' => $amount,
            ]);

            return $payment;
        } catch (\Exception $e) {
            $payment->markAsFailed(null, $e->getMessage());

            Log::error('Error al iniciar pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Procesar callback/webhook del gateway
     *
     * @param  string  $gateway  Nombre del gateway
     * @param  array  $data  Datos recibidos del gateway
     *
     * @throws \Exception Si hay un error al procesar
     */
    public function handleCallback(string $gateway, array $data): Payment
    {
        $gatewayInstance = $this->factory::make($gateway);

        // Verificar firma primero
        if (! $gatewayInstance->verifySignature($data)) {
            Log::error('Firma inválida en callback', [
                'gateway' => $gateway,
                'data' => $data,
            ]);
            throw new \Exception('Firma inválida');
        }

        // Procesar el callback
        $payment = $gatewayInstance->handleCallback($data);

        // Disparar eventos según el estado
        if ($payment->is_completed) {
            event(new PaymentCompleted($payment));
        } elseif ($payment->is_failed) {
            event(new PaymentFailed($payment));
        }

        return $payment;
    }

    /**
     * Buscar un pago por referencia
     */
    public function findByReference(string $referenceNumber): ?Payment
    {
        return Payment::where('reference_number', $referenceNumber)->first();
    }

    /**
     * Buscar un pago por transaction_id del gateway
     */
    public function findByTransactionId(string $transactionId, string $gateway): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)
            ->where('gateway', $gateway)
            ->first();
    }

    /**
     * Obtener pagos de un modelo específico
     *
     * @return Collection
     */
    public function getPaymentsFor(Model $payable)
    {
        return Payment::where('paymentable_type', get_class($payable))
            ->where('paymentable_id', $payable->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
