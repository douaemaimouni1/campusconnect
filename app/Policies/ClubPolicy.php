<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    /**
     * Seul le président du club peut gérer les demandes d'adhésion
     * et de participation aux événements de son club.
     */
    public function manage(User $user, Club $club): bool
    {
        return $user->id === $club->president_id;
    }
}