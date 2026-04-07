<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscriptions\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionSuspendedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly TenantSubscription $subscription,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tenant suspendido por mora')
            ->line('Una suscripción fue suspendida por vencimiento de gracia.')
            ->line('Tenant: '.$this->subscription->tenant_id)
            ->line('Suscripción: #'.$this->subscription->id)
            ->action('Ver tenant', url('/admin/tenants/'.$this->subscription->tenant_id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_suspended',
            'tenant_id' => $this->subscription->tenant_id,
            'subscription_id' => $this->subscription->id,
        ];
    }
}
