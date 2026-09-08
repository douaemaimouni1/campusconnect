<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPost;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\ClubMembershipRequested;
use App\Notifications\EventRegistrationRequested;
use App\Services\CloudinaryUploadService;
use Illuminate\Notifications\DatabaseNotification;
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

    // --------- Modale liste des participants à un événement (président uniquement) ---------
    public bool $showEventParticipantsModal = false;
    public ?int $participantsEventId = null;

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

        // --- Nouveau : arrivée depuis l'accueil ou la page événement via le
        // lien "Modifier" (?edit_post=ID) — rouvre directement la modale
        // d'édition sur ce post précis. openEditPostModal() vérifie déjà
        // que le poste appartient bien à CE club et que l'utilisateur est
        // bien le président, donc aucune vérification supplémentaire n'est
        // nécessaire ici.
        if ($postId = request()->query('edit_post')) {
            $this->openEditPostModal((int) $postId);
        }
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

    /**
     * Supprime, s'il existe, la notification déjà envoyée à $presidentId pour
     * une demande donnée (adhésion ou participation). Utilisée quand
     * l'utilisateur annule lui-même sa demande avant que le président ait pu
     * répondre : la notification n'a alors plus lieu d'exister.
     */
    private function deleteRelatedNotification(string $type, string $dataKey, int $recordId, ?int $presidentId): void
    {
        if (! $presidentId) {
            return;
        }

        DatabaseNotification::where('notifiable_type', User::class)
            ->where('notifiable_id', $presidentId)
            ->where('type', $type)
            ->where('data->' . $dataKey, $recordId)
            ->delete();
    }

    public function toggleMembersModal()
    {
        $this->showMembersModal = ! $this->showMembersModal;
    }

    // --------- Liste des participants à un événement ---------

    public function openEventParticipantsModal(int $eventId)
    {
        abort_if($this->club->president_id !== Auth::id(), 403);

        $this->participantsEventId = $eventId;
        $this->showEventParticipantsModal = true;
    }

    public function closeEventParticipantsModal()
    {
        $this->showEventParticipantsModal = false;
        $this->participantsEventId = null;
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
            $this->existingEventImageUrl = $post->event->image;
        } else {
            $this->postType = 'post';
            $this->postContent = $post->content;
            $this->existingPostImageUrl = $post->image;
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

    public function savePost(CloudinaryUploadService $cloudinary)
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
                $eventData['image'] = $cloudinary->upload($this->eventImage, 'events');
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
                $postData['image'] = $cloudinary->upload($this->postImage, 'posts');
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

    /**
     * Envoie une demande d'adhésion, et notifie le président du club
     * (s'il en a un : un club sans président n'a personne à notifier ici).
     */
    public function joinClub()
    {
        if ($this->membershipStatus !== null) {
            return;
        }

        $membership = ClubMembership::create([
            'user_id' => Auth::id(),
            'club_id' => $this->club->id,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        if ($this->club->president_id) {
            User::find($this->club->president_id)->notify(new ClubMembershipRequested($membership));
        }

        $this->refreshStatuses();
    }

    /**
     * Annule une demande d'adhésion en attente : supprime la notification
     * déjà envoyée au président (si elle existe encore), puis la demande.
     */
    public function cancelMembership()
    {
        $membership = ClubMembership::where('user_id', Auth::id())
            ->where('club_id', $this->club->id)
            ->where('status', 'pending')
            ->first();

        if ($membership) {
            $this->deleteRelatedNotification(
                ClubMembershipRequested::class,
                'membership_id',
                $membership->id,
                $this->club->president_id,
            );

            $membership->delete();
        }

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

    /**
     * Inscription rapide à un événement du club (depuis la liste des posts).
     * Notifie le président du club organisateur, s'il y en a un.
     */
    public function joinEvent(int $eventId)
    {
        $alreadyExists = EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $registration = EventRegistration::create([
            'user_id' => Auth::id(),
            'event_id' => $eventId,
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        if ($this->club->president_id) {
            User::find($this->club->president_id)->notify(new EventRegistrationRequested($registration));
        }

        $this->refreshStatuses();
    }

    /**
     * Annule une inscription à un événement (en attente ou déjà confirmée).
     * Si elle était encore en attente, supprime aussi la notification déjà
     * envoyée au président (sinon, elle a déjà été traitée/marquée lue par
     * ailleurs, rien à nettoyer).
     */
    public function cancelEventRegistration(int $eventId)
    {
        $registration = EventRegistration::where('user_id', Auth::id())
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($registration) {
            if ($registration->status === 'pending') {
                $this->deleteRelatedNotification(
                    EventRegistrationRequested::class,
                    'registration_id',
                    $registration->id,
                    $this->club->president_id,
                );
            }

            $registration->delete();
        }

        $this->refreshStatuses();
    }

    public function render()
    {
        $this->refreshStatuses();

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

        $eventParticipants = collect();

        if ($this->showEventParticipantsModal && $this->participantsEventId) {
            $eventParticipants = EventRegistration::where('event_id', $this->participantsEventId)
                ->where('status', 'confirmed')
                ->with('user')
                ->orderBy('registered_at')
                ->get();
        }

        return view('livewire.clubs.show', [
            'posts' => $posts,
            'members' => $members,
            'eventParticipants' => $eventParticipants,
        ]);
    }

}