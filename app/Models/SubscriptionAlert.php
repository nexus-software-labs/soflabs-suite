<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Subscriptions\TenantSubscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAlert extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'type',
        'level',
        'title',
        'message',
        'context',
        'notified_at',
    ];

    protected $casts = [
        'context' => 'array',
        'notified_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }
}
