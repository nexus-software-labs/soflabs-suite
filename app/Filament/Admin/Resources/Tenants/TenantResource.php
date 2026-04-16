<?php

namespace App\Filament\Admin\Resources\Tenants;

use App\Filament\Admin\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Admin\Resources\Tenants\Pages\EditTenant;
use App\Filament\Admin\Resources\Tenants\Pages\ListTenants;
use App\Filament\Admin\Resources\Tenants\Pages\ViewTenant;
use App\Filament\Admin\Resources\Tenants\RelationManagers\DomainsRelationManager;
use App\Filament\Admin\Resources\Tenants\RelationManagers\ModulesRelationManager;
use App\Filament\Admin\Resources\Tenants\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Admin\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Admin\Resources\Tenants\Schemas\TenantInfolist;
use App\Filament\Admin\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\Onboarding\TenantOnboardingOrchestrator;
use App\Services\Subscriptions\SubscriptionService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationLabel = 'Inquilinos';

    protected static ?string $modelLabel = 'Inquilino';

    protected static ?string $pluralModelLabel = 'Inquilinos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    /**
     * @return list<string>
     */
    public static function moduleKeys(): array
    {
        return ['inventory', 'packages', 'printing'];
    }

    /**
     * @param  array<string, mixed>  $rawFormState
     */
    public static function afterCreate(Tenant $record, array $rawFormState): void
    {
        $record->loadMissing('plan');

        app(TenantOnboardingOrchestrator::class)->provisionAfterTenantCreated($record, $rawFormState);
    }

    /**
     * @param  array<string, mixed>  $rawFormState
     */
    public static function afterSave(Tenant $record, array $rawFormState): void
    {
        $selected = $rawFormState['active_modules'] ?? [];
        $selected = is_array($selected) ? $selected : [];

        self::syncTenantModules($record, $selected);

        $billingCycle = (string) ($rawFormState['billing_cycle'] ?? 'monthly');
        $currentSubscription = $record->subscriptions()->latest('created_at')->first();
        if ($record->plan !== null && $currentSubscription !== null) {
            app(SubscriptionService::class)->changePlan(
                subscription: $currentSubscription,
                newPlan: $record->plan,
                newBillingCycle: $billingCycle,
                prorate: true,
                gateway: (string) ($rawFormState['billing_gateway'] ?? 'cybersource'),
            );
        }
    }

    /**
     * @param  array<int, string>  $selectedModules
     */
    public static function syncTenantModules(Tenant $tenant, array $selectedModules): void
    {
        $allowed = self::moduleKeys();
        $selected = array_values(array_intersect($selectedModules, $allowed));

        foreach ($allowed as $module) {
            $isActive = in_array($module, $selected, true);

            TenantModule::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->getKey(),
                    'module' => $module,
                ],
                [
                    'is_active' => $isActive,
                    'activated_at' => $isActive ? now() : null,
                ],
            );
        }
    }

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DomainsRelationManager::class,
            SubscriptionsRelationManager::class,
            ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'view' => ViewTenant::route('/{record}'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['company_name', 'id'];
    }
}
