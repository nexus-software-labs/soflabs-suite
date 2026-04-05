<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Middleware\InjectTenantContext;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Rutas de tenant (subdominio / dominio de inquilino)
|--------------------------------------------------------------------------
| Middleware: web → identificación de tenancy → InjectTenantContext →
| PreventAccessFromCentralDomains. Los subgrupos por módulo añaden module:*.
|
| El GET /login Inertia lo registra el panel Filament app apuntando a
| {@see TenantAuthController::showLogin}. El POST /login va aquí (Inertia).
| El POST /logout lo sigue registrando Filament; la lógica pasa por
| {@see TenantAuthController::logout} vía el contenedor y la redirección
| del panel app en {@see \App\Http\Responses\Filament\AppPanelLogoutResponse}.
|--------------------------------------------------------------------------
*/

$tenantMiddleware = [
    'web',
    config('tenancy.identification_middleware'),
    InjectTenantContext::class,
    PreventAccessFromCentralDomains::class,
];

Route::middleware($tenantMiddleware)->group(function () {
    $registerTenantInertiaAuth = function (): void {
        Route::post('/login', [TenantAuthController::class, 'login'])
            ->middleware('guest');

    };

    if (filled(config('app.domain'))) {
        Route::domain('{tenant}.'.config('app.domain'))->group($registerTenantInertiaAuth);
    } else {
        $registerTenantInertiaAuth();
    }

    Route::prefix('inventory')
        ->name('inventory.')
        ->middleware('module:inventory')
        ->group(function () {
            Route::get('/', fn () => Inertia::render('Inventory/Dashboard'))->name('dashboard');
        });

    Route::prefix('packages')
        ->name('packages.')
        ->middleware('module:packages')
        ->group(function () {
            Route::get('/', fn () => Inertia::render('Packages/Dashboard'))->name('dashboard');
        });

    Route::prefix('printing')
        ->name('printing.')
        ->middleware('module:printing')
        ->group(function () {
            Route::get('/', fn () => Inertia::render('Printing/Dashboard'))->name('dashboard');
        });
});
