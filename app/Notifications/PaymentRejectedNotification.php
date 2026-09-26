<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PaymentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification implements ShouldQueue
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
            ->subject(__('Payment verification update - AURA CARD'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We could not verify your payment request for plan :plan.', [
                'plan' => $this->paymentRequest->plan?->getLocalizedName(),
            ]))
            ->line(__('Reason: :reason', [
                'reason' => $this->paymentRequest->rejection_reason ?? __('Receipt could not be verified with the bank.'),
            ]))
            ->line(__('You can submit a new payment proof or contact support if you believe this was an error.'))
            ->action(__('Submit New Payment Proof', []), url('/pricing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_rejected',
            'payment_request_id' => $this->paymentRequest->id,
            'reason' => $this->paymentRequest->rejection_reason,
        ];
    }
}
