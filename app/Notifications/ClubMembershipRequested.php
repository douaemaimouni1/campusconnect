<?php

namespace App\Notifications;

use App\Models\ClubMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubMembershipRequested extends Notification
{
    use Queueable;

    public function __construct(
        public ClubMembership $membership,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->membership->loadMissing(['club', 'user']);

        return [
            'membership_id' => $this->membership->id,
            'club_id' => $this->membership->club_id,
            'club_name' => $this->membership->club->name,
            'requester_id' => $this->membership->user_id,
            'requester_name' => $this->membership->user->name,
        ];
    }
}