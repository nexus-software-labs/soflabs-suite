<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Middleware\InjectTenantContext;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Rutas Inertia / HTTP del inquilino (no Filament)
|--------------------------------------------------------------------------
| Filament panel "app" (staff del tenant) se registra en AppPanelProvider con
| el mismo host {tenant}.APP_DOMAIN. Este archivo es para rutas adicionales
| (módulos Inertia, APIs del tenant, etc.).
|
| Orden de middleware: web → InitializeTenancyBySubdomain → InjectTenantContext
| → PreventAccessFromCentralDomains (bloquea hosts listados en tenancy.central_domains).
|
| TODO / roadmap (añadir en los bloques indicados):
| - Inertia: flujo de pedidos de impresión por tenant (si sale del central).
| - API REST del tenant (prefijo /api/v1/tenant/...) con Sanctum + policies.
| - Webhooks de proveedores (envío, pagos) firmados por tenant.
| - Descarga de reportes / exports async.
|--------------------------------------------------------------------------
*/

$tenantMiddleware = [
    'web',
    config('tenancy.identification_middleware'),
    InjectTenantContext::class,
    'tenant.subscription',
    PreventAccessFromCentralDomains::class,
];

$registerTenantHttpRoutes = function (): void {
    /*
     * GET /login — acceso para usuarios del tenant (mismo formulario Inertia que el panel).
     * GET /panel/login — backoffice Filament (TenantAuthController::showLogin).
     * POST /login — envío del formulario (ambas pantallas).
     */
    Route::get('/login', [TenantAuthController::class, 'showPublicLogin'])
        ->middleware('guest')
        ->name('tenant.login');

    Route::post('/login', [TenantAuthController::class, 'login'])
        ->middleware('guest');

    /*
     * --------------------------------------------------------------------------
     * Aquí: rutas Inertia/API propias del tenant (roadmap)
     * --------------------------------------------------------------------------
     * Ejemplos a colocar cuando existan controladores y páginas:
     *
     * Route::prefix('print-orders')->name('tenant.print-orders.')->group(function () {
     *     Route::get('/create', ...)->name('create');
     * });
     *
     * Route::prefix('api/tenant')->middleware('auth:sanctum')->group(function () {
     *     // ...
     * });
     * --------------------------------------------------------------------------
     */

    Route::prefix('inventory')
        ->name('inventory.')
        ->middleware('module:inventory')
        ->group(function (): void {
            Route::get('/', fn () => Inertia::render('Inventory/Dashboard'))->name('dashboard');
            // TODO: CRUD Inertia, APIs de stock, ajustes por sucursal, etc.
        });

    Route::prefix('packages')
        ->name('packages.')
        ->middleware('module:packages')
        ->group(function (): void {
            Route::get('/', fn () => Inertia::render('Packages/Dashboard'))->name('dashboard');
            // TODO: envíos, guías, integración couriers, tracking cliente.
        });

    Route::prefix('printing')
        ->name('printing.')
        ->middleware('module:printing')
        ->group(function (): void {
            Route::get('/', fn () => Inertia::render('Printing/Dashboard'))->name('dashboard');
            // TODO: cola de trabajo taller, estados, asignación por sucursal.
        });
};

/*
 * Con APP_DOMAIN definido, estas rutas solo existen en {tenant}.dominio.
 * Así no compiten con el host central y el subdominio queda alineado con Filament app.
 *
 * Sin dominio central (entornos mínimos), el grupo sin ->domain() evita romper
 * artisan serve; tenancy por subdominio en ese modo no es representativo.
 */
if (filled(config('app.domain'))) {
    Route::middleware($tenantMiddleware)
        ->domain('{tenant}.'.config('app.domain'))
        ->group($registerTenantHttpRoutes);
} else {
    Route::middleware($tenantMiddleware)->group($registerTenantHttpRoutes);
}
