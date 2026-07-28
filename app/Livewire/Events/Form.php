<?php

namespace App\Livewire\Events;

use App\Models\Club;
use App\Models\ClubPost;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?Club $club = null;
    public ?Event $event = null;

    public $title = '';
    public $description = '';
    public $location = '';
    public $date = '';
    public $capacity = '';

    public $image;
    public $existingImageUrl = null;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'date' => 'required|date|after:now',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|max:4096',
        ];
    }

    public function mount(?Club $club = null, ?Event $event = null)
    {
        if ($event && $event->exists) {
            // --------- MODIFICATION ---------
            abort_if($event->club->president_id !== Auth::id(), 403);

            $this->event = $event;
            $this->club = $event->club;
            $this->title = $event->title;
            $this->description = $event->description;
            $this->location = $event->location;
            $this->date = $event->date->format('Y-m-d\TH:i');
            $this->capacity = $event->capacity;

            if ($event->image) {
                $this->existingImageUrl = asset('storage/' . $event->image);
            }
        } else {
            // --------- CRÉATION ---------
            abort_if($club->president_id !== Auth::id(), 403);

            $this->club = $club;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'date' => $this->date,
            'capacity' => $this->capacity,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('events/images', 'public');
        }

        if ($this->event) {
            $this->event->update($data);
            session()->flash('success', 'Événement modifié avec succès 🎉');
        } else {
            $data['club_id'] = $this->club->id;
            $this->event = Event::create($data);

            // Publie automatiquement une annonce dans le fil du club
            ClubPost::create([
                'club_id' => $this->club->id,
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'content' => "📅 Nouvel événement : {$this->event->title}",
            ]);

            session()->flash('success', 'Événement créé avec succès 🎉');
        }

        return redirect()->route('events.show', $this->event);
    }

    public function render()
    {
        return view('livewire.events.form');
    }
}