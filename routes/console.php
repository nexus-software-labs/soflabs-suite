<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenancy:migrate', function (): void {
    $this->call('migrate', config('tenancy.migration_parameters', []));
})->purpose('Migrar la base de datos del tenant actual (invocar dentro de $tenant->run()).');
