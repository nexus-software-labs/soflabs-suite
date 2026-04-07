<?php

namespace App\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneService
{
    protected static $phoneUtil;

    protected static function getPhoneUtil()
    {
        if (! self::$phoneUtil) {
            self::$phoneUtil = PhoneNumberUtil::getInstance();
        }

        return self::$phoneUtil;
    }

    /**
     * Validar teléfono según país
     */
    public static function validatePhone(string $phone, string $countryCode): bool
    {
        try {
            $phoneUtil = self::getPhoneUtil();
            $phoneNumber = $phoneUtil->parse($phone, $countryCode);

            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Formatear teléfono
     */
    public static function formatPhone(string $phone, string $countryCode, string $format = 'INTERNATIONAL'): ?string
    {
        try {
            $phoneUtil = self::getPhoneUtil();
            $phoneNumber = $phoneUtil->parse($phone, $countryCode);

            $formatType = match ($format) {
                'NATIONAL' => PhoneNumberFormat::NATIONAL,
                'E164' => PhoneNumberFormat::E164,
                'RFC3966' => PhoneNumberFormat::RFC3966,
                default => PhoneNumberFormat::INTERNATIONAL,
            };

            return $phoneUtil->format($phoneNumber, $formatType);
        } catch (NumberParseException $e) {
            return null;
        }
    }

    /**
     * Obtener ejemplo de número para un país
     */
    public static function getExampleNumber(string $countryCode): ?string
    {
        try {
            $phoneUtil = self::getPhoneUtil();
            $exampleNumber = $phoneUtil->getExampleNumber($countryCode);

            return $phoneUtil->format($exampleNumber, PhoneNumberFormat::NATIONAL);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener código de país
     */
    public static function getCountryCodeForRegion(string $countryCode): ?int
    {
        try {
            $phoneUtil = self::getPhoneUtil();

            return $phoneUtil->getCountryCodeForRegion($countryCode);
        } catch (\Exception $e) {
            return null;
        }
    }
}
