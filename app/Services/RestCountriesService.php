<?php

namespace App\Services;

class RestCountriesService
{
    protected static $baseUrl = 'https://restcountries.com/v3.1';

    /**
     * Obtener todos los países
     * Solo devuelve El Salvador y Guatemala para el registro
     */
    public static function getAllCountries(): array
    {
        return [
            'SV' => [
                'name' => 'El Salvador',
                'native_name' => [],
                'phone_code' => '+503',
                'languages' => ['es' => 'Español'],
            ],
            'GT' => [
                'name' => 'Guatemala',
                'native_name' => [],
                'phone_code' => '+502',
                'languages' => ['es' => 'Español'],
            ],
        ];
    }

    /**
     * Obtener un país específico
     */
    public static function getCountry(string $countryCode): ?array
    {
        $countries = self::getAllCountries();

        return $countries[$countryCode] ?? null;
    }

    /**
     * Formatear código telefónico
     */
    protected static function formatPhoneCode(array $idd): string
    {
        if (empty($idd['root'])) {
            return '';
        }

        $root = $idd['root'];
        $suffixes = $idd['suffixes'] ?? [];

        if (empty($suffixes)) {
            return $root;
        }

        return $root.$suffixes[0];
    }

    /**
     * Países de respaldo si la API falla
     */
    protected static function getFallbackCountries(): array
    {
        return [
            'SV' => ['name' => 'El Salvador', 'phone_code' => '+503'],
            'DO' => ['name' => 'República Dominicana', 'phone_code' => '+1-809'],
            'GT' => ['name' => 'Guatemala', 'phone_code' => '+502'],
            'HN' => ['name' => 'Honduras', 'phone_code' => '+504'],
            'NI' => ['name' => 'Nicaragua', 'phone_code' => '+505'],
            'CR' => ['name' => 'Costa Rica', 'phone_code' => '+506'],
            'PA' => ['name' => 'Panamá', 'phone_code' => '+507'],
            'MX' => ['name' => 'México', 'phone_code' => '+52'],
            'US' => ['name' => 'Estados Unidos', 'phone_code' => '+1'],
            'ES' => ['name' => 'España', 'phone_code' => '+34'],
        ];
    }
}
