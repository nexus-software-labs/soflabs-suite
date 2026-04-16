<?php

declare(strict_types=1);

namespace App\Filament\App;

use App\Filament\Admin\Resources\Branches\BranchResource;
use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Filament\Admin\Resources\Payments\PaymentResource;
use App\Filament\Admin\Resources\PrintOrders\PrintOrderResource;
use App\Filament\Admin\Resources\Promotions\PromotionResource;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Filament\App\Plugins\TenantModulePluginsRegistration;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Middleware\CheckTenantSubscriptionStatus;
use App\Http\Middleware\Filament\ShareTenantParameterForFilamentUrls;
use App\Modules\Inventory\Filament\InventoryPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

final class AppPanelProvider extends PanelProvider
{
    /**
     * {@inheritDoc}
     *
     * Los plugins de módulo (widgets, etc.) se aplican en el boot del panel vía
     * {@see TenantModulePluginsRegistration}. Los recursos de inventario se
     * declaran aquí para que sus rutas existan al registrar rutas de Filament;
     * navegación y acceso siguen condicionados al módulo en cada recurso.
     */
    public function register(): void
    {
        parent::register();
    }

    public function panel(Panel $panel): Panel
    {
        $baseDomain = config('app.domain');
        $tenantDomain = filled($baseDomain)
            ? '{tenant}.'.$baseDomain
            : null;

        $panel = $panel
            ->id('app')
            ->path('panel')
            ->homeUrl('/dashboard')
            ->login([TenantAuthController::class, 'showLogin'])
            // La ruta POST de cierre de sesión se registra junto al login (no existe ->logout() en Panel v5).
            ->profile()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugin(
                FilamentShieldPlugin::make()
                    ->scopeToTenant(true)
            )
            ->plugin(new TenantModulePluginsRegistration)
            ->resources(array_merge([
                UserResource::class,
                BranchResource::class,
                CustomerResource::class,
                PrintOrderResource::class,
                PaymentResource::class,
                PromotionResource::class,
            ], InventoryPlugin::resourceClasses()))
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([])
            ->middleware([
                ShareTenantParameterForFilamentUrls::class,
                InitializeTenancyBySubdomain::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckTenantSubscriptionStatus::class,
                PreventAccessFromCentralDomains::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if (filled($tenantDomain)) {
            $panel = $panel->domain($tenantDomain);
        }

        return $panel;
    }
}
