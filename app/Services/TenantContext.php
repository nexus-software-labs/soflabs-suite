<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Subscriptions\TenantSubscription;
use App\Models\Tenant;
use App\Models\User;

class TenantContext
{
    public ?Tenant $tenant = null;

    public ?Branch $branch = null;

    public ?User $user = null;

    public array $modules = [];

    public ?TenantSubscription $subscription = null;

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules, true);
    }

    /**
     * @param  list<string>  $modules
     */
    public function hasAnyModule(array $modules): bool
    {
        foreach ($modules as $module) {
            if ($this->hasModule($module)) {
                return true;
            }
        }

        return false;
    }

    public function isMainBranch(): bool
    {
        return $this->branch !== null && $this->branch->is_main;
    }

    public function isTenantAdmin(): bool
    {
        return $this->user !== null && $this->user->is_tenant_admin;
    }

    public function getBranchId(): ?string
    {
        return $this->branch?->id;
    }

    public function getTenantId(): ?string
    {
        return $this->tenant?->id;
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->subscription === null) {
            return false;
        }

        return in_array($this->subscription->status, [
            TenantSubscription::STATUS_ACTIVE,
            TenantSubscription::STATUS_PAST_DUE,
        ], true);
    }
}
