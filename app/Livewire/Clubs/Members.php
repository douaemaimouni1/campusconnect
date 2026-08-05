<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Members extends Component
{
    public Club $club;

    public function mount(Club $club)
    {
        $this->club = $club;
    }

    /**
     * Retire un membre accepté du club. Réservé au président
     * (vérification serveur, même si le bouton n'est visible que pour lui
     * côté vue).
     */
    public function removeMember(int $membershipId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->where('status', 'accepted')
            ->delete();

        session()->flash('success', 'Membre retiré du club.');
    }

    public function render()
    {
        $members = $this->club->memberships()
            ->where('status', 'accepted')
            ->with('user')
            ->latest('responded_at')
            ->get();

        return view('livewire.clubs.members', [
            'members' => $members,
        ]);
    }
}