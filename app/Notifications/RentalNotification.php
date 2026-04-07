<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalNotification extends Notification
{
    private $title;
    private $message;
    private $rentalId;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $rentalId, $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->rentalId = $rentalId;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'rental_id' => $this->rentalId,
            'type' => $this->type, // info, success, warning, error
        ];
    }
}
