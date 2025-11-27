<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    public $organizationName;
    public $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct($organizationName, $rejectionReason = null)
    {
        $this->organizationName = $organizationName;
        $this->rejectionReason = $rejectionReason;
    }

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
            ->subject('Volunteer Application Update - Bridge Pen Pal')
            ->markdown('vendor.notifications.application-rejected', [
                'userName' => $notifiable->name,
                'organizationName' => $this->organizationName,
                'rejectionReason' => $this->rejectionReason,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
