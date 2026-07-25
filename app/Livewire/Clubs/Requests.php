<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\EventRegistration;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Requests extends Component
{
    public Club $club;

    public function mount(Club $club)
    {
        // Bloque l'accès si l'utilisateur connecté n'est pas
        // le président de ce club (erreur 403 automatique sinon).
        $this->authorize('manage', $club);

        $this->club = $club;
    }

    /**
     * Accepte une demande d'adhésion au club.
     */
    public function acceptMembership(int $membershipId)
    {
        ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);
    }

    /**
     * Refuse une demande d'adhésion : suppression directe, pas de trace.
     */
    public function rejectMembership(int $membershipId)
    {
        ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->delete();
    }

    /**
     * Accepte une demande de participation à un événement du club.
     */
    public function acceptEventRegistration(int $registrationId)
    {
        EventRegistration::where('id', $registrationId)
            ->whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->update([
                'status' => 'confirmed',
            ]);
    }

    /**
     * Refuse une demande de participation à un événement : suppression directe.
     */
    public function rejectEventRegistration(int $registrationId)
    {
        EventRegistration::where('id', $registrationId)
            ->whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->delete();
    }

    public function render()
    {
        $membershipRequests = ClubMembership::where('club_id', $this->club->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest('requested_at')
            ->get();

        $eventRequests = EventRegistration::whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->where('status', 'pending')
            ->with(['user', 'event'])
            ->latest('registered_at')
            ->get();

        return view('livewire.clubs.requests', [
            'membershipRequests' => $membershipRequests,
            'eventRequests' => $eventRequests,
        ]);
    }
}