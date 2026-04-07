<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscriptions\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentFailedNotification extends Notification
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
            ->subject('Fallo de cobro en suscripción')
            ->line('Falló el cobro de renovación de una suscripción.')
            ->line('Tenant: '.$this->subscription->tenant_id)
            ->line('Suscripción: #'.$this->subscription->id)
            ->line('Gracia hasta: '.optional($this->subscription->grace_ends_at)?->format('d/m/Y H:i'))
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
            'type' => 'subscription_payment_failed',
            'tenant_id' => $this->subscription->tenant_id,
            'subscription_id' => $this->subscription->id,
        ];
    }
}
