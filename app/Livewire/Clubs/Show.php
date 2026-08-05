<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPost;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Club $club;

    public ?string $membershipStatus = null;

    public array $eventRegistrations = [];

    public bool $showMembersModal = false;

    // --------- Modale de post (post simple OU post + événement) ---------
    public bool $showPostModal = false;
    public string $postType = 'post'; // 'post' ou 'event'

    public ?ClubPost $editingPost = null;
    public ?Event $editingEvent = null;

    // Champs "post simple"
    public string $postContent = '';
    public $postImage = null;
    public ?string $existingPostImageUrl = null;

    // Champs "événement"
    public string $eventTitle = '';
    public string $eventDescription = '';
    public string $eventLocation = '';
    public string $eventDate = '';
    public $eventCapacity = '';
    public $eventImage = null;
    public ?string $existingEventImageUrl = null;

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

    // --------- Gestion des posts / événements ---------

    public function togglePostModal()
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        $this->resetPostForm();
        $this->showPostModal = ! $this->showPostModal;
    }

    public function openEditPostModal(int $postId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        $post = ClubPost::where('id', $postId)
            ->where('club_id', $this->club->id)
            ->with('event')
            ->firstOrFail();

        $this->editingPost = $post;

        if ($post->event) {
            $this->postType = 'event';
            $this->editingEvent = $post->event;
            $this->eventTitle = $post->event->title;
            $this->eventDescription = $post->event->description;
            $this->eventLocation = $post->event->location;
            $this->eventDate = $post->event->date->format('Y-m-d\TH:i');
            $this->eventCapacity = $post->event->capacity;
            $this->existingEventImageUrl = $post->event->image ? asset('storage/' . $post->event->image) : null;
        } else {
            $this->postType = 'post';
            $this->postContent = $post->content;
            $this->existingPostImageUrl = $post->image ? asset('storage/' . $post->image) : null;
        }

        $this->showPostModal = true;
    }

    protected function resetPostForm()
    {
        $this->editingPost = null;
        $this->editingEvent = null;
        $this->postType = 'post';

        $this->postContent = '';
        $this->postImage = null;
        $this->existingPostImageUrl = null;

        $this->eventTitle = '';
        $this->eventDescription = '';
        $this->eventLocation = '';
        $this->eventDate = '';
        $this->eventCapacity = '';
        $this->eventImage = null;
        $this->existingEventImageUrl = null;

        $this->resetErrorBag();
    }

    public function savePost()
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        if ($this->postType === 'event') {

            $this->validate([
                'eventTitle' => 'required|string|max:255',
                'eventDescription' => 'required|string',
                'eventLocation' => 'required|string|max:255',
                'eventDate' => 'required|date',
                'eventCapacity' => 'required|integer|min:1',
                'eventImage' => 'nullable|image|max:4096',
            ]);

            $eventData = [
                'title' => $this->eventTitle,
                'description' => $this->eventDescription,
                'location' => $this->eventLocation,
                'date' => $this->eventDate,
                'capacity' => $this->eventCapacity,
            ];

            if ($this->eventImage) {
                $eventData['image'] = $this->eventImage->store('events', 'public');
            }

            if ($this->editingEvent) {
                $this->editingEvent->update($eventData);
                $event = $this->editingEvent;
            } else {
                $eventData['club_id'] = $this->club->id;
                $event = Event::create($eventData);
            }

            $postContent = "📅 Nouvel événement : {$event->title}";

            if ($this->editingPost) {
                $this->editingPost->update(['content' => $postContent, 'event_id' => $event->id]);
            } else {
                ClubPost::create([
                    'club_id' => $this->club->id,
                    'user_id' => Auth::id(),
                    'event_id' => $event->id,
                    'content' => $postContent,
                ]);
            }

        } else {

            $this->validate([
                'postContent' => 'required|string|max:5000',
                'postImage' => 'nullable|image|max:4096',
            ]);

            $postData = [
                'content' => $this->postContent,
            ];

            if ($this->postImage) {
                $postData['image'] = $this->postImage->store('posts', 'public');
            }

            if ($this->editingPost) {
                $this->editingPost->update($postData);
            } else {
                $postData['club_id'] = $this->club->id;
                $postData['user_id'] = Auth::id();
                ClubPost::create($postData);
            }

        }

        $this->showPostModal = false;
        $this->resetPostForm();
    }

    public function deletePost(int $postId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        ClubPost::where('id', $postId)
            ->where('club_id', $this->club->id)
            ->whereNull('event_id')
            ->delete();
    }

    public function deleteEvent(int $eventId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        $event = Event::where('id', $eventId)
            ->where('club_id', $this->club->id)
            ->first();

        if ($event) {
            ClubPost::where('club_id', $this->club->id)
                ->where('event_id', $event->id)
                ->delete();

            $event->delete();
        }
    }

    // --------- Adhésion club ---------

    public function removeMember(int $membershipId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        ClubMembership::where('id', $membershipId)
            ->where('club_id', $this->club->id)
            ->where('status', 'accepted')
            ->delete();
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

    // --------- Inscription événements ---------

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
            ->latest()
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