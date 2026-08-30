<?php
namespace App\Livewire\Events;
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
    public function render()
    {
        $this->event->loadCount([
            'eventRegistrations as participants_count' => fn ($q) => $q->where('status', 'confirmed'),
        ]);
        $this->event->load('club');
        return view('livewire.events.show');
    }
}