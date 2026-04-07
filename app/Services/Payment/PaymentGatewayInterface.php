<?php

namespace App\Services\Payment;

use App\Models\Core\Payment;

/**
 * Interface común para todos los gateways de pago
 *
 * Todos los gateways deben implementar esta interfaz para garantizar
 * consistencia en el sistema de pagos.
 */
interface PaymentGatewayInterface
{
    /**
     * Iniciar un nuevo pago
     *
     * @param  Payment  $payment  El modelo Payment con la información del pago
     * @param  array  $options  Opciones adicionales específicas del gateway
     * @return Payment El payment actualizado con redirect_url y otros datos necesarios
     */
    public function initiate(Payment $payment, array $options = []): Payment;

    /**
     * Procesar la respuesta/callback del gateway
     *
     * @param  array  $data  Datos recibidos del gateway
     * @return Payment El payment actualizado con el resultado
     */
    public function handleCallback(array $data): Payment;

    /**
     * Verificar la firma/autenticidad de la respuesta del gateway
     *
     * @param  array  $data  Datos recibidos del gateway
     * @return bool True si la firma es válida
     */
    public function verifySignature(array $data): bool;

    /**
     * Obtener el nombre del gateway
     */
    public function getName(): string;

    /**
     * Verificar si el gateway está disponible/configurado
     */
    public function isAvailable(): bool;
}
