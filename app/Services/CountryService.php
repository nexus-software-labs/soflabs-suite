<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CountryService
{
    /**
     * Detectar país por IP
     */
    public static function detectCountryByIp(?string $ip = null): string
    {
        try {
            $ip = $ip ?? request()->ip();

            // Usar un servicio gratuito de geolocalización
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['countryCode'] ?? 'SV';

                // Verificar si el país está en nuestra configuración
                if (config("countries.{$countryCode}")) {
                    return $countryCode;
                }
            }
        } catch (\Exception $e) {
            // Si falla, retornar país por defecto
            return 'SV';
        }

        return 'SV'; // País por defecto: El Salvador
    }

    /**
     * Obtener configuración del país
     */
    public static function getCountryConfig(string $countryCode): ?array
    {
        return config("countries.{$countryCode}");
    }

    /**
     * Obtener todos los países disponibles
     */
    public static function getAllCountries(): array
    {
        return config('countries');
    }

    /**
     * Validar documento según país
     */
    public static function validateDocument(string $document, string $countryCode): bool
    {
        $config = self::getCountryConfig($countryCode);

        if (! $config) {
            return false;
        }

        return preg_match($config['document']['regex'], $document) === 1;
    }

    /**
     * Validar teléfono según país
     */
    public static function validatePhone(string $phone, string $countryCode): bool
    {
        $config = self::getCountryConfig($countryCode);

        if (! $config) {
            return false;
        }

        return preg_match($config['phone']['regex'], $phone) === 1;
    }

    /**
     * Formatear documento según país
     */
    public static function formatDocument(string $document, string $countryCode): string
    {
        // Eliminar caracteres no alfanuméricos
        $clean = preg_replace('/[^0-9A-Z]/i', '', strtoupper($document));

        $config = self::getCountryConfig($countryCode);
        if (! $config) {
            return $clean;
        }

        // Aplicar máscara según el país
        $mask = $config['document']['mask'];
        $formatted = '';
        $cleanIndex = 0;

        for ($i = 0; $i < strlen($mask) && $cleanIndex < strlen($clean); $i++) {
            if ($mask[$i] === '0' || $mask[$i] === 'A') {
                $formatted .= $clean[$cleanIndex];
                $cleanIndex++;
            } else {
                $formatted .= $mask[$i];
            }
        }

        return $formatted;
    }
}
