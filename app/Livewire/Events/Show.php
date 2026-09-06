<?php

namespace App\Livewire\Events;

use App\Models\ClubPost;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventRegistrationRequested;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Event $event;
    public ?string $registrationStatus = null;
    public bool $isOrganizer = false;

    // --- Nouveau : modal liste des participants (organisateur uniquement) ---
    public bool $showParticipantsModal = false;

    // --- Nouveau : id du post qui annonce cet événement, pour le lien "Modifier" ---
    // (renvoie vers clubs.show, qui rouvre directement la modale d'édition sur ce post)
    public ?int $relatedPostId = null;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->isOrganizer = $event->club->president_id === Auth::id();
        $this->refreshStatus();
    }

    protected function refreshStatus()
    {
        $this->registrationStatus = EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $this->event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->value('status');
    }

    public function joinEvent()
    {
        if ($this->isOrganizer || $this->registrationStatus !== null) {
            return;
        }

        // Bloque la demande si l'événement est déjà complet (capacité
        // comptée sur les inscriptions confirmées uniquement, cohérent
        // avec le compteur affiché sur la page).
        $confirmedCount = EventRegistration::where('event_id', $this->event->id)
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedCount >= $this->event->capacity) {
            session()->flash('error', 'Cet événement est complet.');
            return;
        }

        $registration = EventRegistration::create([
            'user_id' => Auth::id(),
            'event_id' => $this->event->id,
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        $this->event->loadMissing('club');

        if ($this->event->club->president_id) {
            User::find($this->event->club->president_id)->notify(new EventRegistrationRequested($registration));
        }

        $this->refreshStatus();
    }

    /**
     * Annule une inscription (en attente ou confirmée). Si elle était
     * encore en attente, supprime aussi la notification déjà envoyée au
     * président du club organisateur.
     */
    public function cancelRegistration()
    {
        $registration = EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $this->event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($registration) {
            if ($registration->status === 'pending') {
                $this->event->loadMissing('club');

                if ($this->event->club->president_id) {
                    DatabaseNotification::where('notifiable_type', User::class)
                        ->where('notifiable_id', $this->event->club->president_id)
                        ->where('type', EventRegistrationRequested::class)
                        ->where('data->registration_id', $registration->id)
                        ->delete();
                }
            }

            $registration->delete();
        }

        $this->refreshStatus();
    }

    // --- Nouveau : ouvrir/fermer le modal, réservé à l'organisateur ---
    public function openParticipantsModal()
    {
        if (! $this->isOrganizer) {
            return;
        }

        $this->showParticipantsModal = true;
    }

    public function closeParticipantsModal()
    {
        $this->showParticipantsModal = false;
    }

    /**
     * Supprime l'événement (et le post qui l'annonce), depuis sa propre
     * page dédiée. Réservé à l'organisateur. Redirige ensuite vers la
     * page du club, puisqu'il n'y a plus de page événement à afficher.
     */
    public function deleteEvent()
    {
        abort_if(! $this->isOrganizer, 403);

        $club = $this->event->club;

        ClubPost::where('event_id', $this->event->id)->delete();
        $this->event->delete();

        return redirect()->route('clubs.show', $club);
    }

    public function render()
    {
        $this->event->loadCount([
            'eventRegistrations as participants_count' => fn ($q) => $q->where('status', 'confirmed'),
        ]);
        $this->event->load('club');

        // --- Nouveau : id du post lié, pour le lien "Modifier" (une seule ligne, pas de relation à ajouter au modèle) ---
        $this->relatedPostId = ClubPost::where('event_id', $this->event->id)->value('id');

        // --- Nouveau : liste des participants confirmés, seulement si le modal est ouvert et qu'on est l'organisateur ---
        $participants = collect();

        if ($this->isOrganizer && $this->showParticipantsModal) {
            $participants = $this->event->eventRegistrations()
                ->where('status', 'confirmed')
                ->with('user')
                ->orderBy('registered_at')
                ->get();
        }

        return view('livewire.events.show', [
            'participants' => $participants,
        ]);
    }
}