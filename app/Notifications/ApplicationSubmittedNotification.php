<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public $organizationName;

    /**
     * Create a new notification instance.
     */
    public function __construct($organizationName)
    {
        $this->organizationName = $organizationName;
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
            ->subject('Volunteer Application Submitted - Bridge Pen Pal')
            ->markdown('vendor.notifications.application-submitted', [
                'userName' => $notifiable->name,
                'organizationName' => $this->organizationName,
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
