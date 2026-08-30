<?php

namespace App\Notifications;

use App\Models\ClubPresidencyTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubPresidencyTransferResponded extends Notification
{
    use Queueable;

    public function __construct(
        public ClubPresidencyTransfer $transfer,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->transfer->loadMissing(['club', 'proposedPresident']);

        return [
            'transfer_id' => $this->transfer->id,
            'club_id' => $this->transfer->club_id,
            'club_name' => $this->transfer->club->name,
            'proposed_president_name' => $this->transfer->proposedPresident->name,
            'status' => $this->transfer->status, // 'accepted' ou 'declined'
        ];
    }
}