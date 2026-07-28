<?php
namespace App\Livewire\Events;
use App\Models\Event;
use App\Models\EventRegistration;
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

        EventRegistration::create([
            'user_id' => Auth::id(),
            'event_id' => $this->event->id,
            'status' => 'pending',
            'registered_at' => now(),
        ]);
        $this->refreshStatus();
    }
    public function cancelRegistration()
    {
        EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $this->event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->delete();
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