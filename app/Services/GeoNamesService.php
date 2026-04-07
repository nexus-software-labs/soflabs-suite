<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoNamesService
{
    protected static $username = 'carranza32';

    protected static $baseUrl = 'http://api.geonames.org/searchJSON';

    /**
     * Nivel ADM1 - Estados / Departamentos / Regiones
     */
    public static function getAdm1(string $countryCode): array
    {
        return Cache::remember("geonames_adm1_{$countryCode}", 86400, function () use ($countryCode) {
            $response = Http::get(self::$baseUrl, [
                'country' => $countryCode,
                'featureCode' => 'ADM1',
                'maxRows' => 1000,
                'username' => self::$username,
            ]);

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json()['geonames'] ?? [])
                ->map(fn ($item) => [
                    'id' => $item['geonameId'],
                    'name' => $item['name'],
                    'code' => $item['adminCode1'] ?? null,
                ])
                ->values()
                ->toArray();
        });
    }

    /**
     * Nivel ADM2 - Ciudades / Municipios / Provincias
     */
    public static function getAdm2(string $countryCode, string $adm1Code): array
    {
        return Cache::remember("geonames_adm2_{$countryCode}_{$adm1Code}", 86400, function () use ($countryCode, $adm1Code) {
            $response = Http::get(self::$baseUrl, [
                'country' => $countryCode,
                'adminCode1' => $adm1Code,
                'featureCode' => 'ADM2',
                'maxRows' => 1000,
                'username' => self::$username,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = collect($response->json()['geonames'] ?? [])
                ->map(fn ($item) => [
                    'id' => $item['geonameId'],
                    'name' => $item['name'],
                    'code' => $item['adminCode2'] ?? null,
                    'latitude' => isset($item['lat']) ? (float) $item['lat'] : null,
                    'longitude' => isset($item['lng']) ? (float) $item['lng'] : null,
                ])
                ->values()
                ->toArray();

            // Algunos países no tienen ADM2 (devuelve vacío)
            return $data;
        });
    }

    /**
     * Nivel ADM3 - Distritos / Barrios / Subregiones (si existen)
     */
    public static function getAdm3(string $countryCode, string $adm1Code, string $adm2Code): array
    {
        return Cache::remember("geonames_adm3_{$countryCode}_{$adm1Code}_{$adm2Code}", 86400, function () use ($countryCode, $adm1Code, $adm2Code) {
            $response = Http::get(self::$baseUrl, [
                'country' => $countryCode,
                'adminCode1' => $adm1Code,
                'adminCode2' => $adm2Code,
                'featureCode' => 'ADM3',
                'maxRows' => 1000,
                'username' => self::$username,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = collect($response->json()['geonames'] ?? [])
                ->map(fn ($item) => [
                    'id' => $item['geonameId'],
                    'name' => $item['name'],
                    'code' => $item['adminCode3'] ?? null,
                ])
                ->values()
                ->toArray();

            // Si ADM3 no existe en el país, devuelve array vacío
            return $data;
        });
    }

    /**
     * Obtener nivel siguiente según jerarquía (para validación dinámica)
     */
    public static function getNextLevel(string $countryCode, ?string $adm1Code = null, ?string $adm2Code = null): array
    {
        if (! $adm1Code) {
            return self::getAdm1($countryCode);
        }

        $adm2 = self::getAdm2($countryCode, $adm1Code);
        if ($adm2) {
            if ($adm2Code) {
                $adm3 = self::getAdm3($countryCode, $adm1Code, $adm2Code);

                return $adm3 ?: [];
            }

            return $adm2;
        }

        return []; // No tiene subdivisiones
    }

    /**
     * Buscar un lugar por nombre dentro del país (y opcionalmente estado).
     * Útil cuando ADM2 no contiene el municipio (p.ej. GeoNames subdivide distinto que Boxful).
     *
     * @return array{id: int, name: string, code: ?string, adminCode1: ?string, adminCode2: ?string}|null
     */
    public static function searchPlaceByName(string $countryCode, string $placeName, ?string $adminCode1 = null): ?array
    {
        $placeName = trim($placeName);
        if ($placeName === '') {
            return null;
        }

        $cacheKey = 'geonames_search_'.md5($countryCode.'_'.$placeName.'_'.($adminCode1 ?? ''));
        $ttl = 86400;

        return Cache::remember($cacheKey, $ttl, function () use ($countryCode, $placeName, $adminCode1) {
            $params = [
                'q' => $placeName,
                'country' => $countryCode,
                'maxRows' => 15,
                'username' => self::$username,
                'featureClass' => 'A',
            ];
            if ($adminCode1 !== null && $adminCode1 !== '') {
                $params['adminCode1'] = $adminCode1;
            }

            $response = Http::get(self::$baseUrl, $params);
            if (! $response->successful()) {
                return null;
            }

            $geonames = $response->json()['geonames'] ?? [];
            if (empty($geonames)) {
                return null;
            }

            $norm = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $placeName) ?: $placeName));
            foreach ($geonames as $g) {
                $gName = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $g['name'] ?? '') ?: ($g['name'] ?? '')));
                $gName = preg_replace('/^(municipio|departamento)de?\s*/iu', '', $gName);
                if ($gName === $norm || str_contains($gName, $norm) || str_contains($norm, $gName)) {
                    return [
                        'id' => $g['geonameId'],
                        'name' => $g['name'],
                        'code' => $g['adminCode2'] ?? $g['adminCode3'] ?? null,
                        'adminCode1' => $g['adminCode1'] ?? null,
                        'adminCode2' => $g['adminCode2'] ?? null,
                    ];
                }
            }

            return null;
        });
    }

    /**
     * Obtener coordenadas (lat, lng) de una ubicación usando Geonames
     */
    public static function getCoordinates(string $countryCode, string $adm1Code, string $adm2Code, ?string $adm3Code = null): ?array
    {
        try {
            // Construir query para búsqueda
            $queryParts = array_filter([$adm3Code, $adm2Code, $adm1Code, $countryCode]);
            $query = implode(', ', $queryParts);

            $response = Http::get(self::$baseUrl, [
                'q' => $query,
                'maxRows' => 1,
                'username' => self::$username,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (isset($data['geonames']) && count($data['geonames']) > 0) {
                $location = $data['geonames'][0];

                return [
                    'latitude' => (float) $location['lat'],
                    'longitude' => (float) $location['lng'],
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo coordenadas de Geonames: '.$e->getMessage());

            return null;
        }
    }
}
