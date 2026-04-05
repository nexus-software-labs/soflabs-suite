<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantProvisioned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Tenant $tenant) {}
}
