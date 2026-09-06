<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\ClubMembershipRequested;
use App\Notifications\ClubMembershipResponded;
use App\Notifications\EventRegistrationRequested;
use App\Notifications\EventRegistrationResponded;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
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
     * Marque comme lue la notification de demande (adhésion ou
     * participation) reçue par le président connecté, quand il traite la
     * demande depuis cette page plutôt qu'en cliquant directement sur la
     * notification. Sans ça, le badge de notifications non lues ne
     * redescendait jamais pour les demandes traitées ici.
     */
    private function markRequestNotificationRead(string $type, string $dataKey, int $recordId): void
    {
        DatabaseNotification::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->where('type', $type)
            ->where('data->' . $dataKey, $recordId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Accepte une demande d'adhésion au club, et notifie le demandeur.
     */
    public function acceptMembership(int $membershipId)
    {
        $membership = ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->with('user')
            ->first();

        if (! $membership) {
            return;
        }

        $membership->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $this->markRequestNotificationRead(ClubMembershipRequested::class, 'membership_id', $membership->id);

        $membership->user->notify(new ClubMembershipResponded($this->club, 'accepted'));
    }

    /**
     * Refuse une demande d'adhésion : suppression directe, pas de trace.
     * On notifie le demandeur AVANT de supprimer, sinon on perdrait la
     * référence à son compte.
     */
    public function rejectMembership(int $membershipId)
    {
        $membership = ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->with('user')
            ->first();

        if (! $membership) {
            return;
        }

        $user = $membership->user;

        $this->markRequestNotificationRead(ClubMembershipRequested::class, 'membership_id', $membership->id);

        $membership->delete();

        $user->notify(new ClubMembershipResponded($this->club, 'rejected'));
    }

    /**
     * Accepte une demande de participation à un événement du club, et
     * notifie le demandeur. Bloque si la capacité de l'événement est déjà
     * atteinte : ne fait rien et affiche un message d'erreur, plutôt que de
     * confirmer au-delà de la capacité (le bouton est aussi caché côté vue,
     * mais cette vérification serveur reste indispensable, on ne fait
     * jamais confiance uniquement à l'affichage).
     */
    public function acceptEventRegistration(int $registrationId)
    {
        $registration = EventRegistration::where('id', $registrationId)
            ->whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->with(['event', 'user'])
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

        $this->markRequestNotificationRead(EventRegistrationRequested::class, 'registration_id', $registration->id);

        $registration->user->notify(new EventRegistrationResponded($registration->event, 'confirmed'));
    }

    /**
     * Refuse une demande de participation à un événement : suppression
     * directe. On notifie le demandeur AVANT de supprimer.
     */
    public function rejectEventRegistration(int $registrationId)
    {
        $registration = EventRegistration::where('id', $registrationId)
            ->whereHas('event', function ($query) {
                $query->where('club_id', $this->club->id);
            })
            ->with(['event', 'user'])
            ->first();

        if (! $registration) {
            return;
        }

        $user = $registration->user;
        $event = $registration->event;

        $this->markRequestNotificationRead(EventRegistrationRequested::class, 'registration_id', $registration->id);

        $registration->delete();

        $user->notify(new EventRegistrationResponded($event, 'rejected'));
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