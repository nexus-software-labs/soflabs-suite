<?php

declare(strict_types=1);

namespace App\Filament\Admin;

use App\Filament\Admin\Resources\Countries\CountryResource;
use App\Filament\Admin\Resources\CustomerTiers\CustomerTierResource;
use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\Admin\Resources\Regions\RegionResource;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Filament\Admin\Widgets\Admin\FailedSubscriptionCharges;
use App\Filament\Admin\Widgets\Admin\SubscriptionAlerts;
use App\Filament\Admin\Widgets\Admin\SubscriptionOverview;
use App\Http\Middleware\EnsureSuperAdmin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $centralDomain = config('app.domain');

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugin(FilamentShieldPlugin::make())
            ->resources([
                PlanResource::class,
                TenantResource::class,
                RegionResource::class,
                CountryResource::class,
                CustomerTierResource::class,
                UserResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                SubscriptionOverview::class,
                SubscriptionAlerts::class,
                FailedSubscriptionCharges::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // Tras Authenticate el usuario ya está resuelto en la petición.
                EnsureSuperAdmin::class,
            ]);

        if (filled($centralDomain)) {
            $panel = $panel->domain($centralDomain);
        }

        return $panel;
    }
}
