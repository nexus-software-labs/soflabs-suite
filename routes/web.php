<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Rutas centrales (landlord)
|--------------------------------------------------------------------------
| Solo deben responder en el host central (APP_DOMAIN / config app.domain),
| no en {tenant}.dominio. Si las dejáramos sin ->domain(), en un subdominio
| coincidirían antes que el tenant y tenancy NO se inicializaría (scopes,
| branches, etc. quedarían mal).
|
| Fortify (login web, registro, etc.) se registra aparte; Filament admin usa
| el mismo dominio central vía AdminPanelProvider.
|
| TODO / roadmap (añadir aquí cuando toque):
| - Landing marketing, blog, legal (páginas estáticas o CMS).
| - API pública central (billing webhooks del SaaS, integraciones landlord).
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\PrintOrderApiController;
use App\Http\Controllers\PrintOrderController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

$centralDomain = config('app.domain');
$useCentralDomain = filled($centralDomain);

$registerCentralWebRoutes = function (): void {
    Route::inertia('/', 'welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ])->name('home');

    Route::middleware(['auth', 'verified'])->group(function (): void {
        Route::inertia('dashboard', 'dashboard')->name('dashboard');
    });

    require __DIR__.'/settings.php';

    Route::prefix('webhooks')->name('webhooks.')->group(function (): void {
        Route::post('/billing/renewal/{gateway}', [BillingWebhookController::class, 'renewal'])
            ->name('billing.renewal');
    });
};

/*
|--------------------------------------------------------------------------
| Flujo público de impresión (Inertia) — hoy central-only
|--------------------------------------------------------------------------
| Si más adelante querés “marca blanca” por tenant (mismo flujo en
| demo.app.test/print-orders), duplicá o mové estas rutas a routes/tenant.php
| dentro del grupo de subdominio (ver comentarios allí).
|
| TODO / roadmap:
| - Rate limiting por IP / por tenant.
| - Versión API JSON consumible desde apps móviles (central vs tenant).
|--------------------------------------------------------------------------
*/
$registerCentralPrintRoutes = function (): void {
    Route::prefix('print-orders')->name('print-orders.')->group(function (): void {
        Route::get('/create', [PrintOrderController::class, 'create'])->name('create');
        Route::post('/', [PrintOrderController::class, 'store'])->name('store');
        Route::get('/success/{orderNumber}', [PrintOrderController::class, 'success'])->name('success');
        Route::get('/track', [PrintOrderController::class, 'track'])->name('track');
        Route::get('/search/{orderNumber}', [PrintOrderController::class, 'show'])->name('show');
        Route::get('/{id}/payment/success', [PrintOrderController::class, 'paymentSuccess'])->name('payment-success');
        Route::get('/{id}/payment/cancel', [PrintOrderController::class, 'paymentCancel'])->name('payment-cancel');

        Route::middleware(['auth'])->group(function (): void {
            Route::get('/my-orders', [PrintOrderController::class, 'myOrders'])->name('my-orders');
            Route::get('/download/{id}', [PrintOrderController::class, 'downloadFile'])->name('download');
            Route::post('/{id}/payment', [PrintOrderController::class, 'initiatePayment'])->name('payment');
        });
    });

    Route::get('/api/print-config', [PrintOrderApiController::class, 'getConfig']);
    Route::post('/api/print-config/analyze-files', [PrintOrderApiController::class, 'analyzeFiles']);
    Route::post('/api/print-config/calculate-price', [PrintOrderApiController::class, 'calculatePrice']);
};

if ($useCentralDomain) {
    Route::domain($centralDomain)->group(function () use ($registerCentralWebRoutes, $registerCentralPrintRoutes): void {
        $registerCentralWebRoutes();
        $registerCentralPrintRoutes();
    });
} else {
    /*
     * Sin dominio central explícito (p. ej. php artisan serve en http://127.0.0.1:8000):
     * todo queda en el mismo host; el subdominio tenant no aplica.
     * Para probar tenancy real, definí APP_DOMAIN y usá URLs tipo demo.midominio.test.
     */
    $registerCentralWebRoutes();
    $registerCentralPrintRoutes();
}
