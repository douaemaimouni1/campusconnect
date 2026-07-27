<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use Illuminate\Support\Facades\Auth;
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


    public function joinClub(int $clubId)
    {
        $userId = Auth::id();

        $club = Club::find($clubId);

        if (! $club || $club->president_id === $userId) {
            return;
        }

        $alreadyExists = ClubMembership::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($alreadyExists) {
            return;
        }

        ClubMembership::create([
            'user_id' => $userId,
            'club_id' => $clubId,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }


    public function cancelRequest(int $clubId)
    {
        ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $clubId)
            ->where('status', 'pending')
            ->delete();
    }

    public function render()
    {
        $clubs = Club::query()
            ->with('president')
            ->withCount([
                'memberships as members_count' => function ($query) {
                    $query->where('status', 'accepted');
                },
            ])
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->category !== '', function ($query) {
                $query->where('category', $this->category);
            })
            ->orderBy('name')
            ->paginate(9);

        $myMemberships = ClubMembership::where('user_id', Auth::id())
            ->whereIn('club_id', $clubs->pluck('id'))
            ->pluck('status', 'club_id');

        $categories = Club::query()
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.clubs.index', [
            'clubs' => $clubs,
            'myMemberships' => $myMemberships,
            'categories' => $categories,
        ]);
    }
}