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
     * Bloque si la capacité de l'événement est déjà atteinte : ne fait rien
     * et affiche un message d'erreur, plutôt que de confirmer au-delà de la
     * capacité (le bouton est aussi caché côté vue, mais cette vérification
     * serveur reste indispensable, on ne fait jamais confiance uniquement à
     * l'affichage).
     */
    public function acceptEventRegistration(int $registrationId)
    {
        $registration = EventRegistration::where('id', $registrationId)
            ->whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->with('event')
            ->first();

        if (! $registration) {
            return;
        }

        $confirmedCount = EventRegistration::where('event_id', $registration->event_id)
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedCount >= $registration->event->capacity) {
            session()->flash('error', "Impossible d'accepter : la capacité de cet événement est déjà atteinte.");
            return;
        }

        $registration->update([
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

        // Pour chaque demande, on calcule si l'événement concerné est déjà
        // complet, afin que la vue puisse cacher le bouton "Accepter".
        $eventRequests->each(function ($request) {
            $confirmedCount = EventRegistration::where('event_id', $request->event_id)
                ->where('status', 'confirmed')
                ->count();

            $request->event_is_full = $confirmedCount >= $request->event->capacity;
        });

        return view('livewire.clubs.requests', [
            'membershipRequests' => $membershipRequests,
            'eventRequests' => $eventRequests,
        ]);
    }
}