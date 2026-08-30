<?php

namespace App\Notifications;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubMembershipResponded extends Notification
{
    use Queueable;

    public function __construct(
        public Club $club,
        public string $status, // 'accepted' ou 'rejected'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'club_id' => $this->club->id,
            'club_name' => $this->club->name,
            'status' => $this->status,
        ];
    }
}