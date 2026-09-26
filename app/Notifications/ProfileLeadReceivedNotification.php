<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Profile;
use App\Models\ProfileLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileLeadReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Profile $profile,
        public ProfileLead $lead
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New inquiry from :name on your AURA CARD profile', ['name' => $this->lead->name]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('You received a new inquiry on your profile :profile:', ['profile' => $this->profile->display_name]))
            ->line(__('From: :sender (:email)', ['sender' => $this->lead->name, 'email' => $this->lead->email]))
            ->when($this->lead->phone, fn ($msg) => $msg->line(__('Phone: :phone', ['phone' => $this->lead->phone])))
            ->line(__('Message: :message', ['message' => $this->lead->message ?? 'No message text provided.']))
            ->action(__('View Inquiries', []), url('/account'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'profile_lead',
            'profile_id' => $this->profile->id,
            'lead_id' => $this->lead->id,
            'sender_name' => $this->lead->name,
            'sender_email' => $this->lead->email,
            'message' => $this->lead->message,
        ];
    }
}
