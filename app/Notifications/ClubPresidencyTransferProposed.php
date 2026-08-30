<?php

namespace App\Notifications;

use App\Models\ClubPresidencyTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubPresidencyTransferProposed extends Notification
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
        $this->transfer->loadMissing(['club', 'currentPresident']);

        return [
            'transfer_id' => $this->transfer->id,
            'club_id' => $this->transfer->club_id,
            'club_name' => $this->transfer->club->name,
            'current_president_name' => $this->transfer->currentPresident->name,
        ];
    }
}