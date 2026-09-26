<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Subscription activated - AURA CARD'))
            ->greeting(__('Congratulations :name!', ['name' => $notifiable->name]))
            ->line(__('Your payment has been approved and your subscription to the :plan plan is now active.', [
                'plan' => $this->subscription->plan?->getLocalizedName(),
            ]))
            ->line(__('You now enjoy unlimited profile edits and all premium digital card features.'))
            ->action(__('Open Profile Builder', []), url('/profile'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_approved',
            'subscription_id' => $this->subscription->id,
            'plan_key' => $this->subscription->plan?->key,
            'starts_at' => $this->subscription->starts_at?->toISOString(),
            'ends_at' => $this->subscription->ends_at?->toISOString(),
        ];
    }
}
