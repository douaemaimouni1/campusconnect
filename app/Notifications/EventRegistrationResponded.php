<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventRegistrationResponded extends Notification
{
    use Queueable;

    public function __construct(
        public Event $event,
        public string $status, // 'confirmed' ou 'rejected'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'status' => $this->status,
        ];
    }
}