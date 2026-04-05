<?php

namespace App\Providers;

use App\Models\Branch;
use App\Observers\BranchObserver;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Responses\Filament\AppPanelLogoutResponse;
use App\Models\User;
use Filament\Auth\Http\Controllers\LogoutController as FilamentLogoutController;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use App\Services\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        $this->app->bind(FilamentLogoutResponseContract::class, AppPanelLogoutResponse::class);

        $this->app->bind(FilamentLogoutController::class, fn (): object => new class
        {
            public function __invoke(): FilamentLogoutResponseContract
            {
                return app(TenantAuthController::class)->logout(request());
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Branch::observe(BranchObserver::class);

        Gate::define('isAdmin', function (User $user): bool {
            return ! $user->is_tenant_admin && $user->is_super_admin;
        });

        Gate::define('viewAdmin', function (User $user): bool {
            return $user->is_super_admin === true;
        });

        $this->app->make(Router::class)
            ->aliasMiddleware('panel', \App\Http\Middleware\Filament\PrepareFilamentPanelContext::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
