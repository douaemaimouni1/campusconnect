<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        // Clubs dont l'utilisateur est membre accepté.
        $memberClubs = $this->user->clubMemberships()
            ->where('status', 'accepted')
            ->with('club')
            ->get()
            ->pluck('club')
            ->filter(); // enlève les clubs supprimés (soft delete) éventuels

        // Clubs dont l'utilisateur est président.
        $presidentClubs = $this->user->clubs()->get();

        // Événements à venir auxquels l'utilisateur participe (accepté ou en attente).
        $eventRegistrations = $this->user->eventRegistrations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('event', function ($query) {
                $query->where('date', '>=', now());
            })
            ->with(['event.club'])
            ->get();

        return view('livewire.profile.show', [
            'memberClubs' => $memberClubs,
            'presidentClubs' => $presidentClubs,
            'eventRegistrations' => $eventRegistrations,
        ]);
    }
}