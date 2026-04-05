<?php

/*
|--------------------------------------------------------------------------
| Rutas centrales (landlord / sin tenant)
|--------------------------------------------------------------------------
| Aquí van la landing, el dashboard central y la configuración de usuarios.
|
| Autenticación (login, registro, recuperación de contraseña, verificación):
| Laravel Fortify — rutas registradas por Fortify (ver FortifyServiceProvider).
|
| Panel de administración Filament: cuando instales filament/filament, el
| instalador suele añadir rutas en bootstrap/app.php o routes/web.php; colócalas
| en esta sección para mantenerlas en el contexto central.
|--------------------------------------------------------------------------
|
| Las rutas de la aplicación por inquilino (módulos inventory, packages,
| printing, etc.) están en routes/tenant.php.
|--------------------------------------------------------------------------
|
| Las rutas de esta sección se limitan al dominio central (APP_DOMAIN) cuando
| está definido, para no competir con el panel Filament del tenant en / del
| subdominio (p. ej. demo.myapp.test/).
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

$registerCentralWebRoutes = function (): void {
    Route::inertia('/', 'welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ])->name('home');

    Route::middleware(['auth', 'verified'])->group(function (): void {
        Route::inertia('dashboard', 'dashboard')->name('dashboard');
    });

    require __DIR__.'/settings.php';
};

if (filled(config('app.domain'))) {
    Route::domain(config('app.domain'))->group($registerCentralWebRoutes);
} else {
    $registerCentralWebRoutes();
}
