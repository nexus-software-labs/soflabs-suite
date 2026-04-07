<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscriptions\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionReactivatedNotification extends Notification
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
            ->subject('Suscripción reactivada')
            ->line('La suscripción se reactivó tras confirmar el pago.')
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
            'type' => 'subscription_reactivated',
            'tenant_id' => $this->subscription->tenant_id,
            'subscription_id' => $this->subscription->id,
        ];
    }
}
