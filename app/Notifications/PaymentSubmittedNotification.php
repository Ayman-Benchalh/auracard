<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PaymentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PaymentRequest $paymentRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Payment proof received - AURA CARD'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We received your payment receipt for plan :plan (:amount :currency).', [
                'plan' => $this->paymentRequest->plan?->getLocalizedName(),
                'amount' => $this->paymentRequest->amount,
                'currency' => $this->paymentRequest->currency,
            ]))
            ->line(__('Our verification team is reviewing your transaction. You will be notified as soon as your subscription is activated.'))
            ->action(__('View Subscription Status', []), url('/subscription'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_submitted',
            'payment_request_id' => $this->paymentRequest->id,
            'amount' => $this->paymentRequest->amount,
            'currency' => $this->paymentRequest->currency,
            'plan_key' => $this->paymentRequest->plan?->key,
        ];
    }
}
