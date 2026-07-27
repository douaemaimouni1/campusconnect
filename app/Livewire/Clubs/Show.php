<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Club $club;

    public ?string $membershipStatus = null;

    public array $eventRegistrations = [];

    public bool $showMembersModal = false;

    public function mount(Club $club)
    {
        $this->club = $club;
        $this->refreshStatuses();
    }

    protected function refreshStatuses()
    {
        $this->membershipStatus = ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $this->club->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->value('status');

        $this->eventRegistrations = EventRegistration::where('user_id', Auth::id())
            ->whereIn('event_id', $this->club->events()->pluck('id'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('status', 'event_id')
            ->toArray();
    }

    public function toggleMembersModal()
    {
        $this->showMembersModal = ! $this->showMembersModal;
    }

    public function joinClub()
    {
        if ($this->membershipStatus !== null) {
            return;
        }

        ClubMembership::create([
            'user_id' => Auth::id(),
            'club_id' => $this->club->id,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->refreshStatuses();
    }

    public function cancelMembership()
    {
        ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $this->club->id)
            ->where('status', 'pending')
            ->delete();

        $this->refreshStatuses();
    }

    public function leaveClub()
    {
        ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $this->club->id)
            ->where('status', 'accepted')
            ->delete();

        $this->refreshStatuses();
    }

    public function joinEvent(int $eventId)
    {
        $alreadyExists = EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyExists) {
            return;
        }

        EventRegistration::create([
            'user_id' => Auth::id(),
            'event_id' => $eventId,
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        $this->refreshStatuses();
    }

    public function cancelEventRegistration(int $eventId)
    {
        EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->delete();

        $this->refreshStatuses();
    }

    public function render()
    {
        $this->club->loadCount([
            'memberships as members_count' => fn ($q) => $q->where('status', 'accepted'),
        ]);

        $posts = $this->club->posts()
            ->with(['user', 'event' => function ($query) {
                $query->withCount([
                    'eventRegistrations as participants_count' => fn ($q) => $q->where('status', 'confirmed'),
                ]);
            }])
            ->get();

        $members = $this->club->memberships()
            ->where('status', 'accepted')
            ->with('user')
            ->get();

        return view('livewire.clubs.show', [
            'posts' => $posts,
            'members' => $members,
        ]);
    }
}