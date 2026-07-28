<?php

namespace App\Livewire\Events;

use App\Models\Club;
use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $events = Event::query()
            ->with('club')
            ->withCount([
                'eventRegistrations as participants_count' => function ($query) {
                    $query->where('status', 'confirmed');
                },
            ])
            ->where('date', '>=', now())
            ->when($this->search !== '', function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->category !== '', function ($query) {
                $query->whereHas('club', function ($q) {
                    $q->where('category', $this->category);
                });
            })
            ->orderBy('date')
            ->paginate(9);

        $categories = Club::query()
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.events.index', [
            'events' => $events,
            'categories' => $categories,
        ]);
    }
}