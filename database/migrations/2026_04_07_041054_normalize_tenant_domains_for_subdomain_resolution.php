<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * InitializeTenancyBySubdomain resuelve el tenant con el segmento del subdominio
     * (p. ej. "demo"), no el FQDN. Corrige filas antiguas tipo "demo.software-labs.test".
     */
    public function up(): void
    {
        $central = config('app.domain');
        if (! is_string($central) || $central === '') {
            return;
        }

        $suffix = '.'.$central;

        DB::table('domains')
            ->select(['id', 'domain'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($suffix): void {
                foreach ($rows as $row) {
                    $domain = (string) $row->domain;
                    if ($domain !== '' && str_ends_with($domain, $suffix)) {
                        DB::table('domains')->where('id', $row->id)->update([
                            'domain' => substr($domain, 0, -strlen($suffix)),
                        ]);
                    }
                }
            });
    }

    /**
     * Reconstruye el FQDN (solo útil si volvieras a usar resolución por host completo).
     */
    public function down(): void
    {
        $central = config('app.domain');
        if (! is_string($central) || $central === '') {
            return;
        }

        $suffix = '.'.$central;

        DB::table('domains')
            ->select(['id', 'domain'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($suffix): void {
                foreach ($rows as $row) {
                    $domain = (string) $row->domain;
                    if ($domain !== '' && ! str_contains($domain, '.')) {
                        DB::table('domains')->where('id', $row->id)->update([
                            'domain' => $domain.$suffix,
                        ]);
                    }
                }
            });
    }
};
