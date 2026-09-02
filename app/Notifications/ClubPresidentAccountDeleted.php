<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifie les Super Admins qu'un président de club a supprimé son compte.
 *
 * Deux cas possibles, distingués par successor_proposed :
 * - true  : un successeur a été choisi par l'ex-président au moment de la
 *           suppression, un ClubPresidencyTransferProposed lui a été envoyé
 *           (il reste à attendre sa réponse, qui elle déclenche déjà
 *           ClubPresidencyTransferResponded vers les admins).
 * - false : aucun successeur éligible, le club est immédiatement passé à
 *           president_id = NULL et nécessite une intervention manuelle
 *           depuis le Dashboard Admin ("Proposer un président").
 *
 * On ne passe pas directement les modèles Club/User au constructeur : au
 * moment de l'appel, dans Edit::submitAccountDeletion(), on a déjà les
 * noms sous la main (via $clubsNeedingSuccessorForDeletion /
 * $clubsWithoutSuccessorForDeletion et $user), pas besoin de recharger les
 * modèles juste pour ça.
 */
class ClubPresidentAccountDeleted extends Notification
{
    use Queueable;

    public function __construct(
        public int $clubId,
        public string $clubName,
        public string $formerPresidentName,
        public bool $successorProposed,
        public ?string $successorName = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'club_id' => $this->clubId,
            'club_name' => $this->clubName,
            'former_president_name' => $this->formerPresidentName,
            'successor_proposed' => $this->successorProposed,
            'successor_name' => $this->successorName,
        ];
    }
}