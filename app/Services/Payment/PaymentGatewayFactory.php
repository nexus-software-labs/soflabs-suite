<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\CashGateway;
use App\Services\Payment\Gateways\CyberSourceGateway;
use App\Services\Payment\Gateways\TransferGateway;
use InvalidArgumentException;

/**
 * Factory para crear instancias de gateways de pago
 */
class PaymentGatewayFactory
{
    /**
     * Crear una instancia del gateway especificado
     *
     * @param  string  $gatewayName  Nombre del gateway (cybersource, cash, transfer)
     *
     * @throws InvalidArgumentException Si el gateway no existe
     */
    public static function make(string $gatewayName): PaymentGatewayInterface
    {
        return match (strtolower($gatewayName)) {
            'cybersource' => new CyberSourceGateway,
            'cash' => new CashGateway,
            'transfer' => new TransferGateway,
            default => throw new InvalidArgumentException("Gateway '{$gatewayName}' no está disponible"),
        };
    }

    /**
     * Verificar si un gateway está disponible
     */
    public static function isAvailable(string $gatewayName): bool
    {
        try {
            $gateway = self::make($gatewayName);

            return $gateway->isAvailable();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener lista de gateways disponibles
     */
    public static function getAvailableGateways(): array
    {
        $gateways = ['cybersource', 'transfer', 'cash'];
        $available = [];

        foreach ($gateways as $gateway) {
            if (self::isAvailable($gateway)) {
                $available[] = $gateway;
            }
        }

        return $available;
    }
}
