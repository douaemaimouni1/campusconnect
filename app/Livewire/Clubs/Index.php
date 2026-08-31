<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\User;
use App\Notifications\ClubMembershipRequested;
use Illuminate\Notifications\DatabaseNotification;
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

    /**
     * Supprime, s'il existe, la notification déjà envoyée au président pour
     * une demande d'adhésion donnée. Utilisée quand l'utilisateur annule
     * lui-même sa demande avant que le président ait pu répondre.
     */
    private function deleteRelatedNotification(int $membershipId, ?int $presidentId): void
    {
        if (! $presidentId) {
            return;
        }

        DatabaseNotification::where('notifiable_type', User::class)
            ->where('notifiable_id', $presidentId)
            ->where('type', ClubMembershipRequested::class)
            ->where('data->membership_id', $membershipId)
            ->delete();
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

        $membership = ClubMembership::create([
            'user_id' => $userId,
            'club_id' => $clubId,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        if ($club->president_id) {
            User::find($club->president_id)->notify(new ClubMembershipRequested($membership));
        }
    }


    public function cancelRequest(int $clubId)
    {
        $membership = ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $clubId)
            ->where('status', 'pending')
            ->first();

        if ($membership) {
            $club = Club::find($clubId);

            $this->deleteRelatedNotification($membership->id, $club?->president_id);

            $membership->delete();
        }
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