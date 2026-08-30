<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventRegistrationRequested extends Notification
{
    use Queueable;

    public function __construct(
        public EventRegistration $registration,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->registration->loadMissing(['event.club', 'user']);

        return [
            'registration_id' => $this->registration->id,
            'event_id' => $this->registration->event_id,
            'event_title' => $this->registration->event->title,
            'club_id' => $this->registration->event->club_id,
            'club_name' => $this->registration->event->club->name,
            'requester_id' => $this->registration->user_id,
            'requester_name' => $this->registration->user->name,
        ];
    }
}