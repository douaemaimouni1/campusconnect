<?php

namespace App\Livewire;

use App\Models\Club;
use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public string $search = '';

    /**
     * Événements à venir, triés par nombre d'inscriptions confirmées.
     * Filtrés par la barre de recherche si elle est remplie.
     */
    #[Computed]
    public function popularEvents()
    {
        return Event::query()
            ->where('date', '>=', now())
            ->with('club')
            ->withCount([
                'eventRegistrations as participants_count' => function ($query) {
                    $query->where('status', 'confirmed');
                },
            ])
            ->when($this->search !== '', function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('participants_count')
            ->take(3)
            ->get();
    }

    /**
     * Clubs triés par nombre de membres acceptés.
     * Filtrés par la barre de recherche si elle est remplie.
     */
    #[Computed]
    public function popularClubs()
    {
        return Club::query()
            ->withCount([
                'memberships as members_count' => function ($query) {
                    $query->where('status', 'accepted');
                },
            ])
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('members_count')
            ->take(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.home');
    }
}