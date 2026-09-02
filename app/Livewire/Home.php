<?php

namespace App\Livewire;

use App\Models\Club;
use App\Models\ClubPost;
use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public string $search = '';

    /**
     * Fil des posts publiés par les clubs (tous clubs confondus), les plus
     * récents en premier. Chaque post charge son club, son auteur, et
     * l'événement lié s'il y en a un (avec le compteur de participants
     * confirmés pour cet événement).
     *
     * whereHas('club') exclut les posts dont le club a été supprimé
     * (soft delete) entre-temps — sans ce filtre, $post->club est null
     * et provoque une erreur à l'affichage.
     */
    #[Computed]
    public function feedPosts()
    {
        return ClubPost::query()
            ->whereHas('club')
            ->with(['club', 'user', 'event' => function ($query) {
                $query->withCount([
                    'eventRegistrations as participants_count' => function ($q) {
                        $q->where('status', 'confirmed');
                    },
                ]);
            }])
            ->when($this->search !== '', function ($query) {
                $query->where('content', 'like', '%' . $this->search . '%')
                    ->orWhereHas('club', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->get();
    }

    /**
     * Tous les événements à venir, triés par nombre d'inscriptions confirmées.
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