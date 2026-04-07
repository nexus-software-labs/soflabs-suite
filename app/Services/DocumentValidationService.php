<?php

namespace App\Services;

class DocumentValidationService
{
    /**
     * Configuración de documentos por país
     * Basada en estándares oficiales de cada país
     */
    protected static $documentConfigs = [
        'UNIVERSAL' => [
            'types' => [
                [
                    'code' => 'PASAPORTE',
                    'name' => 'Pasaporte',
                    'format' => 'Alfanumérico',
                    'regex' => '/^[A-Z0-9]{6,9}$/',
                    'length' => null,
                    'description' => 'Pasaporte internacional',
                ],
            ],
        ],
        'SV' => [
            'types' => [
                [
                    'code' => 'DUI',
                    'name' => 'Documento Único de Identidad',
                    'format' => '00000000-0',
                    'regex' => '/^\d{8}-\d$/',
                    'length' => 10,
                    'description' => 'Formato: 12345678-9',
                ],
                [
                    'code' => 'PASAPORTE',
                    'name' => 'Pasaporte',
                    'format' => 'A00000000',
                    'regex' => '/^[A-Z]\d{8}$/',
                    'length' => 9,
                    'description' => 'Formato: E12345678',
                ],
                [
                    'code' => 'CARNET_RESIDENTE',
                    'name' => 'Carnet de Residente',
                    'format' => '00000000-0',
                    'regex' => '/^\d{8}-\d$/',
                    'length' => 10,
                    'description' => 'Documento para extranjeros con residencia en El Salvador. Formato: 12345678-9',
                ],
            ],
        ],
        'DO' => [
            'types' => [
                [
                    'code' => 'CEDULA',
                    'name' => 'Cédula de Identidad',
                    'format' => '000-0000000-0',
                    'regex' => '/^\d{3}-?\d{7}-?\d$/',
                    'length' => 13,
                    'description' => 'Formato: 402-1234567-8',
                ],
                [
                    'code' => 'RNC',
                    'name' => 'Registro Nacional del Contribuyente',
                    'format' => '000-00000-0',
                    'regex' => '/^\d{3}-?\d{5}-?\d$/',
                    'length' => 11,
                    'description' => 'Formato: 130-12345-6',
                ],
                [
                    'code' => 'CARNET_RESIDENTE',
                    'name' => 'Carnet de Residente',
                    'format' => '000-0000000-0',
                    'regex' => '/^\d{3}-?\d{7}-?\d$/',
                    'length' => 13,
                    'description' => 'Documento para extranjeros con residencia en República Dominicana. Formato: 402-1234567-8',
                ],
            ],
        ],
        'GT' => [
            'types' => [
                [
                    'code' => 'DPI',
                    'name' => 'Documento Personal de Identificación',
                    'format' => '0000 00000 0000',
                    'regex' => '/^\d{4}\s?\d{5}\s?\d{4}$/',
                    'length' => 16,
                    'description' => 'Formato: 1234 12345 1234',
                ],
                [
                    'code' => 'CUI',
                    'name' => 'Código Único de Identificación',
                    'format' => '0000000000000',
                    'regex' => '/^\d{13}$/',
                    'length' => 13,
                    'description' => 'Formato: 1234567890123',
                ],
                [
                    'code' => 'CARNET_RESIDENTE',
                    'name' => 'Carnet de Residente',
                    'format' => '0000000000000',
                    'regex' => '/^\d{13}$/',
                    'length' => 13,
                    'description' => 'Documento para extranjeros con residencia en Guatemala. Formato: 1234567890123',
                ],
            ],
        ],
        'HN' => [
            'types' => [
                [
                    'code' => 'IDENTIDAD',
                    'name' => 'Tarjeta de Identidad',
                    'format' => '0000-0000-00000',
                    'regex' => '/^\d{4}-?\d{4}-?\d{5}$/',
                    'length' => 15,
                    'description' => 'Formato: 0801-1990-12345',
                ],
                [
                    'code' => 'CARNET_RESIDENTE',
                    'name' => 'Carnet de Residente',
                    'format' => '0000-0000-00000',
                    'regex' => '/^\d{4}-?\d{4}-?\d{5}$/',
                    'length' => 15,
                    'description' => 'Documento para extranjeros con residencia en Honduras. Formato: 0801-1990-12345',
                ],
            ],
        ],
        'NI' => [
            'types' => [
                [
                    'code' => 'CEDULA',
                    'name' => 'Cédula de Identidad',
                    'format' => '000-000000-0000A',
                    'regex' => '/^\d{3}-?\d{6}-?\d{4}[A-Z]$/',
                    'length' => 16,
                    'description' => 'Formato: 001-123456-0001N',
                ],
            ],
        ],
        'CR' => [
            'types' => [
                [
                    'code' => 'CEDULA',
                    'name' => 'Cédula de Identidad',
                    'format' => '0-0000-0000',
                    'regex' => '/^\d-?\d{4}-?\d{4}$/',
                    'length' => 11,
                    'description' => 'Formato: 1-2345-6789',
                ],
                [
                    'code' => 'DIMEX',
                    'name' => 'Documento de Identificación Migratoria',
                    'format' => '000000000000',
                    'regex' => '/^\d{11,12}$/',
                    'length' => 12,
                    'description' => 'Formato: 123456789012',
                ],
                [
                    'code' => 'CARNET_RESIDENTE',
                    'name' => 'Carnet de Residente',
                    'format' => '000000000000',
                    'regex' => '/^\d{11,12}$/',
                    'length' => 12,
                    'description' => 'Documento para extranjeros con residencia en Costa Rica. Formato: 123456789012',
                ],
            ],
        ],
        'PA' => [
            'types' => [
                [
                    'code' => 'CEDULA',
                    'name' => 'Cédula de Identidad Personal',
                    'format' => 'P-000-0000',
                    'regex' => '/^[A-Z0-9]{1,2}-\d{1,4}-\d{1,6}$/',
                    'length' => null,
                    'description' => 'Formato: 8-123-1234 o PE-1-123',
                ],
            ],
        ],
        'MX' => [
            'types' => [
                [
                    'code' => 'CURP',
                    'name' => 'Clave Única de Registro de Población',
                    'format' => 'AAAA000000AAAAAA00',
                    'regex' => '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/',
                    'length' => 18,
                    'description' => 'Formato: HEGG560427MVZRRL04',
                ],
                [
                    'code' => 'RFC',
                    'name' => 'Registro Federal de Contribuyentes',
                    'format' => 'AAAA000000AAA',
                    'regex' => '/^[A-Z&Ñ]{3,4}\d{6}[A-Z0-9]{3}$/',
                    'length' => 13,
                    'description' => 'Formato: HEGG560427XXX',
                ],
            ],
        ],
        'US' => [
            'types' => [
                [
                    'code' => 'SSN',
                    'name' => 'Social Security Number',
                    'format' => '000-00-0000',
                    'regex' => '/^\d{3}-?\d{2}-?\d{4}$/',
                    'length' => 11,
                    'description' => 'Formato: 123-45-6789',
                ],
            ],
        ],
        'ES' => [
            'types' => [
                [
                    'code' => 'DNI',
                    'name' => 'Documento Nacional de Identidad',
                    'format' => '00000000A',
                    'regex' => '/^\d{8}[A-Z]$/',
                    'length' => 9,
                    'description' => 'Formato: 12345678Z',
                ],
                [
                    'code' => 'NIE',
                    'name' => 'Número de Identificación de Extranjero',
                    'format' => 'A0000000A',
                    'regex' => '/^[XYZ]\d{7}[A-Z]$/',
                    'length' => 9,
                    'description' => 'Formato: X1234567L',
                ],
            ],
        ],
        'AR' => [
            'types' => [
                [
                    'code' => 'DNI',
                    'name' => 'Documento Nacional de Identidad',
                    'format' => '00.000.000',
                    'regex' => '/^\d{7,8}$|^\d{2}\.\d{3}\.\d{3}$/',
                    'length' => null, // Variable 7-10
                    'description' => 'Formato: 12345678 o 12.345.678',
                ],
            ],
        ],
        'CO' => [
            'types' => [
                [
                    'code' => 'CC',
                    'name' => 'Cédula de Ciudadanía',
                    'format' => '0.000.000.000',
                    'regex' => '/^\d{1,3}\.\d{3}\.\d{3}\.\d{3}$/',
                    'length' => 15,
                    'description' => 'Formato: 1.234.567.890',
                ],
            ],
        ],
        'PE' => [
            'types' => [
                [
                    'code' => 'CC',
                    'name' => 'Cédula de Ciudadanía',
                    'format' => '0000000000',
                    'regex' => '/^\d{6,10}$/',
                    'length' => null, // Variable 6-10
                    'description' => 'Formato: 1234567890',
                ],
            ],
        ],
        'CL' => [
            'types' => [
                [
                    'code' => 'RUT',
                    'name' => 'Rol Único Tributario',
                    'format' => '00.000.000-0',
                    'regex' => '/^\d{1,2}\.\d{3}\.\d{3}-[\dkK]$/',
                    'length' => 12,
                    'description' => 'Formato: 12.345.678-9',
                ],
            ],
        ],
    ];

    /**
     * Obtener tipos de documento para un país
     */
    public static function getDocumentTypes(string $countryCode): array
    {
        return self::$documentConfigs[$countryCode]['types'] ?? [];
    }

    /**
     * Validar documento
     */
    public static function validate(string $document, string $countryCode, ?string $documentType = null): bool
    {
        $types = self::getDocumentTypes($countryCode);

        if (empty($types)) {
            return true;
        }

        foreach ($types as $type) {
            if ($documentType && $type['code'] !== $documentType) {
                continue;
            }

            if (preg_match($type['regex'], $document)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener configuración del primer tipo de documento
     */
    public static function getPrimaryDocumentConfig(string $countryCode): ?array
    {
        $types = self::getDocumentTypes($countryCode);

        return $types[0] ?? null;
    }

    /**
     * Obtener toda la configuración de documentos
     */
    public static function getAllDocumentConfigs(): array
    {
        return self::$documentConfigs;
    }
}
