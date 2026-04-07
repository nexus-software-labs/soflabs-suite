<?php

namespace App\Services\Payment\Gateways;

use App\Models\Core\Payment;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Gateway de CyberSource - Implementación completa
 */
class CyberSourceGateway implements PaymentGatewayInterface
{
    protected string $accessKey;

    protected string $profileId;

    protected string $secretKey;

    protected string $environment;

    protected string $endpoint;

    public function __construct()
    {
        $this->accessKey = config('services.cybersource.access_key') ?? '';
        $this->profileId = config('services.cybersource.profile_id') ?? '';
        $this->secretKey = config('services.cybersource.secret_key') ?? '';
        $this->environment = config('services.cybersource.environment', 'test');
        $this->endpoint = 'hosted';

        if (empty($this->accessKey) || empty($this->profileId) || empty($this->secretKey)) {
            throw new \RuntimeException(
                'CyberSource no está configurado. Verifica que CYBERSOURCE_ACCESS_KEY, CYBERSOURCE_PROFILE_ID y CYBERSOURCE_SECRET_KEY estén definidos en tu archivo .env'
            );
        }
    }

    /**
     * Iniciar un nuevo pago con CyberSource Hosted Checkout
     */
    public function initiate(Payment $payment, array $options = []): Payment
    {
        $payable = $payment->paymentable;

        if (! $payable) {
            throw new \Exception('No se puede iniciar el pago: modelo relacionado no encontrado');
        }

        $transactionUuid = uniqid();

        $customerName = $payment->customer_name ?? $this->getCustomerName($payable);
        $customerEmail = $payment->customer_email ?? $this->getCustomerEmail($payable);
        $customerPhoneRaw = $payment->customer_phone ?? $this->getCustomerPhone($payable);
        $customerPhone = $this->normalizePhone($customerPhoneRaw);

        // Log para debug del teléfono
        if ($customerPhoneRaw !== $customerPhone) {
            Log::debug('Teléfono normalizado', [
                'original' => $customerPhoneRaw,
                'normalizado' => $customerPhone,
            ]);
        }

        if (empty($customerEmail)) {
            Log::warning('Email del cliente vacío, usando valor por defecto', [
                'payment_id' => $payment->id,
            ]);
        }

        $nameParts = $this->splitName($customerName ?? 'Cliente');

        $billingAddress = $this->getBillingAddress($payable, $payment);

        $baseUrl = config('app.url');
        $returnUrl = $baseUrl.'/payment-result';
        $cancelUrl = $options['cancel_url'] ?? $baseUrl.'/payment/cancel/'.$payment->id;

        // Parámetros base para CyberSource
        $params = [
            'access_key' => $this->accessKey,
            'profile_id' => $this->profileId,
            'transaction_uuid' => $transactionUuid,
            'signed_field_names' => '',
            'unsigned_field_names' => '',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'locale' => $options['locale'] ?? 'es',
            'transaction_type' => 'sale',
            'reference_number' => $payment->reference_number,
            'amount' => number_format($payment->amount, 2, '.', ''),
            'currency' => strtoupper($payment->currency ?: 'USD'),
            'payment_method' => 'card',

            // Datos del cliente
            'bill_to_forename' => $nameParts['forename'] ?: 'Cliente',
            'bill_to_surname' => $nameParts['surname'] ?: 'Sin Apellido',
            'bill_to_email' => $customerEmail ?: ($payable->customer->email ?? 'test@ejemplo.com'),
            'bill_to_address_line1' => $billingAddress['line1'] ?: 'Calle Test 123',
            'bill_to_address_city' => $billingAddress['city'] ?: 'San Salvador',
            'bill_to_address_state' => $billingAddress['state'] ?: 'SV',
            'bill_to_address_country' => $billingAddress['country'] ?: 'SV',
            'bill_to_address_postal_code' => $billingAddress['postal_code'] ?: '01101',
            'bill_to_phone' => $customerPhone ?: '22222222',
            'override_custom_receipt_page' => $returnUrl,
            'override_custom_cancel_page' => $cancelUrl,
        ];

        // Campos que se firman
        $signedFields = [
            'access_key',
            'profile_id',
            'transaction_uuid',
            'signed_field_names',
            'unsigned_field_names',
            'signed_date_time',
            'locale',
            'transaction_type',
            'reference_number',
            'amount',
            'currency',
            'payment_method',
            'bill_to_forename',
            'bill_to_surname',
            'bill_to_email',
            'bill_to_address_line1',
            'bill_to_address_city',
            'bill_to_address_state',
            'bill_to_address_country',
            'bill_to_address_postal_code',
            'bill_to_phone',
            'override_custom_receipt_page',
            'override_custom_cancel_page',
        ];

        $params['signed_field_names'] = implode(',', $signedFields);
        // Volver a los unsigned fields originales - no agregar service_fee porque causa problemas
        $params['unsigned_field_names'] = 'card_type,card_number,card_expiry_date,card_cvn';

        // Generar firma
        $signature = $this->generateSignature($params);
        $params['signature'] = $signature;

        // $params['override_custom_receipt_page'] = $returnUrl;
        // $params['override_custom_cancel_page'] = $cancelUrl;

        $endpointType = $this->endpoint;
        $endpointUrl = $this->getDefaultEndpoint($endpointType);

        // Log para debug
        $signatureString = $this->getSignatureString($params);
        Log::info('=== PARÁMETROS CYBERSOURCE GENERADOS ===', [
            'reference' => $params['reference_number'],
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'transaction_uuid' => $params['transaction_uuid'],
            'signed_fields_count' => count($signedFields),
            'signature_length' => strlen($signature),
            'signature_string' => $signatureString,
            'signature' => $signature,
            'access_key' => substr($this->accessKey, 0, 10).'...',
            'profile_id' => $this->profileId,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'endpoint' => $endpointUrl,
            'all_params' => $params,
        ]);

        // Guardar información en el payment
        $payment->update([
            'transaction_uuid' => $transactionUuid,
            'signature' => $signature,
            'signed_field_names' => $params['signed_field_names'],
            'redirect_url' => route('payment.redirect', ['payment' => $payment->id]),
            'metadata' => array_merge($payment->metadata ?? [], [
                'cybersource_params' => $params,
                'cybersource_endpoint' => $endpointUrl,
                'endpoint_type' => $this->endpoint,
            ]),
        ]);

        Log::info('Pago CyberSource iniciado', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference_number,
            'amount' => $payment->amount,
            'transaction_uuid' => $transactionUuid,
        ]);

        return $payment;
    }

    /**
     * Procesar callback del gateway
     */
    public function handleCallback(array $data): Payment
    {
        Log::info('=== CALLBACK CYBERSOURCE ===', [
            'all_data' => $data,
            'decision' => $data['decision'] ?? 'N/A',
            'reason_code' => $data['reason_code'] ?? 'N/A',
            'transaction_id' => $data['transaction_id'] ?? 'N/A',
        ]);

        // CyberSource puede enviar req_reference_number o reference_number
        $referenceNumber = $data['req_reference_number'] ?? $data['reference_number'] ?? null;

        if (! $referenceNumber) {
            Log::error('Reference number no encontrado', [
                'available_keys' => array_keys($data),
                'data_sample' => array_slice($data, 0, 10),
            ]);
            throw new \Exception('Reference number no encontrado en la respuesta de CyberSource');
        }

        Log::info('Buscando pago por reference', ['reference' => $referenceNumber]);

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (! $payment) {
            Log::error('Pago no encontrado', [
                'reference' => $referenceNumber,
                'available_references' => Payment::pluck('reference_number')->toArray(),
            ]);
            throw new \Exception("Pago no encontrado con reference: {$referenceNumber}");
        }

        Log::info('Pago encontrado', [
            'payment_id' => $payment->id,
            'current_status' => $payment->status,
        ]);

        // Actualizar información de la transacción
        $payment->update([
            'transaction_id' => $data['transaction_id'] ?? null,
            'decision' => $data['decision'] ?? null,
            'reason_code' => $data['reason_code'] ?? null,
            'reason_message' => $this->getReasonMessage($data['reason_code'] ?? ''),
            'gateway_response' => $data,
        ]);

        // Procesar según la decisión
        $decision = $data['decision'] ?? 'UNKNOWN';
        $reasonCode = $data['reason_code'] ?? null;
        $message = $data['message'] ?? null;

        if ($decision === 'ACCEPT') {
            $payment->markAsCompleted($data);

            Log::info('✓✓✓ PAGO EXITOSO ✓✓✓', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
            ]);
        } elseif ($decision === 'REVIEW') {
            $payment->markAsProcessing();
            Log::warning('⚠ Pago en revisión', ['payment_id' => $payment->id]);
        } else {
            $errorMessage = $this->getReasonMessage($reasonCode ?? '');

            // Verificar si es un error relacionado con Service Fee
            $isServiceFeeError = (isset($data['service_fee_rmsg']) &&
                                $data['service_fee_rmsg'] &&
                                str_contains(strtolower($data['service_fee_rmsg']), 'service fee')) ||
                               (isset($data['message']) &&
                                str_contains(strtolower($data['message']), 'service fee'));

            if ($message) {
                $errorMessage = $message.($errorMessage ? ' ('.$errorMessage.')' : '');
            }

            if ($isServiceFeeError) {
                $errorMessage = 'El procesador de pagos no está configurado para procesar esta transacción. Por favor, contacta al soporte para resolver este problema.';

                Log::warning('⚠ Error de Service Fee detectado - Problema de configuración en CyberSource', [
                    'payment_id' => $payment->id,
                    'service_fee_rmsg' => $data['service_fee_rmsg'] ?? 'N/A',
                    'message' => $data['message'] ?? 'N/A',
                    'reason_code' => $reasonCode,
                    'decision' => $decision,
                    'solución' => 'El perfil de CyberSource necesita tener Service Fee deshabilitado o correctamente configurado. Contactar a CyberSource Support.',
                ]);
            }

            $payment->markAsFailed(
                $reasonCode,
                $errorMessage,
                $data
            );

            Log::error('✗✗✗ PAGO RECHAZADO/ERROR ✗✗✗', [
                'payment_id' => $payment->id,
                'decision' => $decision,
                'reason_code' => $reasonCode,
                'message' => $message,
                'error_message' => $errorMessage,
                'is_service_fee_error' => $isServiceFeeError,
            ]);
        }

        return $payment;
    }

    /**
     * Verificar firma de respuesta
     * IMPORTANTE: Debe ser exactamente igual a generateSignature
     */
    public function verifySignature(array $data): bool
    {
        if (! isset($data['signed_field_names']) || ! isset($data['signature'])) {
            Log::error('Firma no encontrada en respuesta', [
                'has_signed_field_names' => isset($data['signed_field_names']),
                'has_signature' => isset($data['signature']),
                'data_keys' => array_keys($data),
            ]);

            return false;
        }

        $signedFieldNames = explode(',', $data['signed_field_names']);
        $dataToSign = [];

        // EXACTAMENTE igual a generateSignature - sin verificar isset
        foreach ($signedFieldNames as $field) {
            $value = $data[$field] ?? '';
            $dataToSign[] = $field.'='.$value;
        }

        $dataString = implode(',', $dataToSign);
        $calculatedSignature = base64_encode(hash_hmac('sha256', $dataString, $this->secretKey, true));

        $isValid = $calculatedSignature === $data['signature'];

        if (! $isValid) {
            Log::error('Firma inválida en callback', [
                'calculated' => $calculatedSignature,
                'received' => $data['signature'],
                'data_string' => $dataString,
                'signed_field_names' => $data['signed_field_names'],
            ]);
        } else {
            Log::info('✓ Firma verificada correctamente', [
                'reference' => $data['req_reference_number'] ?? 'N/A',
            ]);
        }

        return $isValid;
    }

    /**
     * Obtener nombre del gateway
     */
    public function getName(): string
    {
        return 'cybersource';
    }

    /**
     * Verificar si está disponible
     */
    public function isAvailable(): bool
    {
        return ! empty($this->accessKey) &&
            ! empty($this->profileId) &&
            ! empty($this->secretKey);
    }

    /**
     * Generar firma HMAC-SHA256
     */
    protected function generateSignature(array $params): string
    {
        if (empty($params['signed_field_names'])) {
            throw new \Exception('signed_field_names no puede estar vacío');
        }

        $signedFieldNames = explode(',', $params['signed_field_names']);
        $dataToSign = [];

        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field.'='.$params[$field];
        }

        $dataString = implode(',', $dataToSign);

        Log::debug('String de firma CyberSource', [
            'data_string' => $dataString,
            'fields_count' => count($dataToSign),
            'signed_field_names' => $params['signed_field_names'],
        ]);

        return base64_encode(hash_hmac('sha256', $dataString, $this->secretKey, true));
    }

    /**
     * Obtener nombre del cliente desde el modelo
     */
    protected function getCustomerName($payable): ?string
    {
        if (isset($payable->customer_name)) {
            return $payable->customer_name;
        }

        if (method_exists($payable, 'customer') && $payable->customer) {
            return $payable->customer->name ?? null;
        }

        if (method_exists($payable, 'user') && $payable->user) {
            return $payable->user->name ?? null;
        }

        return null;
    }

    /**
     * Obtener email del cliente
     */
    protected function getCustomerEmail($payable): ?string
    {
        if (isset($payable->customer_email)) {
            return $payable->customer_email;
        }

        if (method_exists($payable, 'customer') && $payable->customer) {
            return $payable->customer->email ?? null;
        }

        if (method_exists($payable, 'user') && $payable->user) {
            return $payable->user->email ?? null;
        }

        return null;
    }

    /**
     * Obtener teléfono del cliente
     */
    protected function getCustomerPhone($payable): ?string
    {
        if (isset($payable->customer_phone)) {
            return $payable->customer_phone;
        }

        if (method_exists($payable, 'customer') && $payable->customer) {
            return $payable->customer->phone ?? null;
        }

        return null;
    }

    /**
     * Obtener dirección de facturación
     */
    protected function getBillingAddress($payable, Payment $payment): array
    {
        // Prioridad: payment > modelo relacionado
        if ($payment->billing_address) {
            return [
                'line1' => $payment->billing_address,
                'city' => $payment->billing_city,
                'state' => $payment->billing_state,
                'country' => $payment->billing_country,
                'postal_code' => $payment->billing_postal_code,
            ];
        }

        // Intentar obtener desde el modelo relacionado
        if (method_exists($payable, 'customerAddress') && $payable->customerAddress) {
            $address = $payable->customerAddress;

            return [
                'line1' => $address->address_line1 ?? '',
                'city' => $address->city ?? '',
                'state' => $address->state ?? '',
                'country' => $address->country ?? 'SV',
                'postal_code' => $address->postal_code ?? '',
            ];
        }

        // Valores por defecto
        return [
            'line1' => '',
            'city' => 'San Salvador',
            'state' => 'SV',
            'country' => 'SV',
            'postal_code' => '',
        ];
    }

    /**
     * Dividir nombre en forename y surname
     */
    protected function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            'forename' => $parts[0] ?? 'Cliente',
            'surname' => $parts[1] ?? 'Sin Apellido',
        ];
    }

    /**
     * Obtener mensaje de razón (códigos estándar CyberSource)
     * Validados en pruebas: 100, 202, 204, 104, 151
     */
    protected function getReasonMessage(string $code): string
    {
        $messages = [
            '100' => 'Transacción exitosa',
            '102' => 'Datos inválidos',
            '104' => 'Transacción duplicada',
            '151' => 'Tiempo de espera agotado',
            '200' => 'Banco no autorizó',
            '202' => 'Rechazado',
            '203' => 'Tarjeta rechazada',
            '204' => 'Fondos insuficientes',
            '231' => 'Número de tarjeta inválido',
        ];

        return $messages[$code] ?? 'Error desconocido';
    }

    /**
     * Obtener endpoint por defecto si no está configurado
     */
    protected function getDefaultEndpoint(string $type): string
    {
        $environment = $this->environment ?? 'test';
        $type = $type ?: 'hosted';

        $defaults = [
            'test' => [
                'hosted' => 'https://testsecureacceptance.cybersource.com/pay',
                'silent' => 'https://testsecureacceptance.cybersource.com/silent/pay',
                'embedded' => 'https://testsecureacceptance.cybersource.com/silent/embedded/pay',
            ],
            'production' => [
                'hosted' => 'https://secureacceptance.cybersource.com/pay',
                'silent' => 'https://secureacceptance.cybersource.com/silent/pay',
                'embedded' => 'https://secureacceptance.cybersource.com/silent/embedded/pay',
            ],
        ];

        return $defaults[$environment][$type] ?? $defaults['test']['hosted'];
    }

    /**
     * Obtener string de firma (para debug)
     * Debe coincidir exactamente con generateSignature
     */
    protected function getSignatureString(array $params): string
    {
        if (empty($params['signed_field_names'])) {
            return '';
        }

        $signedFieldNames = explode(',', $params['signed_field_names']);
        $dataToSign = [];

        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field.'='.$params[$field];
        }

        return implode(',', $dataToSign);
    }

    /**
     * Normalizar número de teléfono para CyberSource
     * Elimina caracteres especiales como +, espacios, guiones, etc.
     */
    protected function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = (string) $phone;
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        return $normalized;
    }
}
