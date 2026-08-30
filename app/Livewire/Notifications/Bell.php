<?php

namespace App\Livewire\Notifications;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPresidencyTransfer;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\ClubMembershipRequested;
use App\Notifications\ClubMembershipResponded;
use App\Notifications\ClubPresidencyTransferProposed;
use App\Notifications\ClubPresidencyTransferResponded;
use App\Notifications\EventRegistrationRequested;
use App\Notifications\EventRegistrationResponded;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bell extends Component
{
    /**
     * Types de notification qui nécessitent une action (accepter/refuser).
     * Ces notifications restent non lues tant que l'action n'a pas été
     * faite, même après ouverture du panneau. Les autres types (réponses,
     * infos) sont marqués comme lus dès que l'utilisateur ouvre le panneau
     * (voir markInformationalAsRead()), mais comptent bien dans le badge
     * TANT qu'ils n'ont pas été vus au moins une fois.
     */
    private const ACTIONABLE_TYPES = [
        ClubPresidencyTransferProposed::class,
        ClubMembershipRequested::class,
        EventRegistrationRequested::class,
    ];

    /**
     * Appelée au clic sur la cloche (en plus du toggle Alpine.js visuel) :
     * marque comme lues toutes les notifications "informatives" (celles qui
     * ne demandent pas d'action), sans toucher à celles qui attendent
     * encore une réponse.
     */
    public function markInformationalAsRead(): void
    {
        Auth::user()->unreadNotifications()
            ->whereNotIn('type', self::ACTIONABLE_TYPES)
            ->get()
            ->each->markAsRead();
    }

    // --------- Présidence de club ---------

    public function acceptTransfer(string $notificationId): void
    {
        $this->respondToTransfer($notificationId, accept: true);
    }

    public function declineTransfer(string $notificationId): void
    {
        $this->respondToTransfer($notificationId, accept: false);
    }

    /**
     * Traite la réponse (acceptation ou refus) d'un successeur proposé, et
     * notifie les super admins du résultat.
     *
     * Sécurité :
     * - $user->notifications()->findOrFail() garantit que la notification
     *   appartient bien à l'utilisateur connecté (sinon 404).
     * - On vérifie explicitement que $transfer->proposed_president_id
     *   correspond à l'utilisateur connecté (sinon 403).
     * - Si le transfert n'est plus "pending" (déjà traité par un double-clic,
     *   ou toute autre voie), on ignore silencieusement la partie
     *   "changement de président" mais on marque quand même la notification
     *   comme lue, pour ne pas laisser une notification bloquée en boucle.
     */
    private function respondToTransfer(string $notificationId, bool $accept): void
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($notificationId);

        $transfer = ClubPresidencyTransfer::find($notification->data['transfer_id'] ?? null);

        abort_if(! $transfer, 404);
        abort_if($transfer->proposed_president_id !== $user->id, 403);

        if ($transfer->status === 'pending') {
            $transfer->club()->update([
                'president_id' => $accept ? $user->id : null,
            ]);

            $transfer->update([
                'status' => $accept ? 'accepted' : 'declined',
                'responded_at' => now(),
            ]);

            $admins = User::where('role', 'superAdmin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new ClubPresidencyTransferResponded($transfer));
            }
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    // --------- Demandes d'adhésion ---------

    public function acceptMembershipRequest(string $notificationId): void
    {
        $this->respondToMembership($notificationId, accept: true);
    }

    public function rejectMembershipRequest(string $notificationId): void
    {
        $this->respondToMembership($notificationId, accept: false);
    }

    private function respondToMembership(string $notificationId, bool $accept): void
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($notificationId);

        $membership = ClubMembership::with(['club', 'user'])->find($notification->data['membership_id'] ?? null);

        // La demande a peut-être déjà été traitée (et donc supprimée si
        // c'était un refus) depuis la page "Gérer les demandes" : rien à
        // faire de plus que marquer la notification comme lue.
        if (! $membership) {
            $notification->markAsRead();

            return;
        }

        abort_if($membership->club->president_id !== $user->id, 403);

        if ($membership->status === 'pending') {
            if ($accept) {
                $membership->update(['status' => 'accepted', 'responded_at' => now()]);

                $membership->user->notify(new ClubMembershipResponded($membership->club, 'accepted'));
            } else {
                $requester = $membership->user;
                $club = $membership->club;

                $membership->delete();

                $requester->notify(new ClubMembershipResponded($club, 'rejected'));
            }
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    // --------- Demandes de participation à un événement ---------

    public function acceptEventRegistrationRequest(string $notificationId): void
    {
        $this->respondToEventRegistration($notificationId, accept: true);
    }

    public function rejectEventRegistrationRequest(string $notificationId): void
    {
        $this->respondToEventRegistration($notificationId, accept: false);
    }

    private function respondToEventRegistration(string $notificationId, bool $accept): void
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($notificationId);

        $registration = EventRegistration::with(['event.club', 'user'])->find($notification->data['registration_id'] ?? null);

        if (! $registration) {
            $notification->markAsRead();

            return;
        }

        abort_if($registration->event->club->president_id !== $user->id, 403);

        if ($registration->status === 'pending') {
            if ($accept) {
                $confirmedCount = EventRegistration::where('event_id', $registration->event_id)
                    ->where('status', 'confirmed')
                    ->count();

                if ($confirmedCount >= $registration->event->capacity) {
                    session()->flash('error', "Impossible d'accepter : la capacité de cet événement est déjà atteinte.");
                } else {
                    $registration->update(['status' => 'confirmed']);

                    $registration->user->notify(new EventRegistrationResponded($registration->event, 'confirmed'));
                }
            } else {
                $requester = $registration->user;
                $event = $registration->event;

                $registration->delete();

                $requester->notify(new EventRegistrationResponded($event, 'rejected'));
            }
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->take(30)->get();

        // Le badge compte TOUTES les notifications non lues, qu'elles soient
        // actionnables ou purement informatives. La différence entre les
        // deux types se joue sur QUAND elles sont marquées comme lues (voir
        // markInformationalAsRead() vs les méthodes respondTo...()),
        // pas sur si elles comptent dans ce total.
        $unreadCount = $user->unreadNotifications()->count();

        // Pour chaque type "actionnable", on recharge le statut RÉEL de
        // l'enregistrement concerné (pas seulement read_at), pour savoir si
        // on doit encore afficher les boutons ou un message d'état.
        $transferIds = $notifications
            ->filter(fn ($n) => $n->type === ClubPresidencyTransferProposed::class)
            ->pluck('data.transfer_id');

        $transfers = ClubPresidencyTransfer::whereIn('id', $transferIds)->get()->keyBy('id');

        $membershipIds = $notifications
            ->filter(fn ($n) => $n->type === ClubMembershipRequested::class)
            ->pluck('data.membership_id');

        $memberships = ClubMembership::whereIn('id', $membershipIds)->get()->keyBy('id');

        $registrationIds = $notifications
            ->filter(fn ($n) => $n->type === EventRegistrationRequested::class)
            ->pluck('data.registration_id');

        $registrations = EventRegistration::whereIn('id', $registrationIds)->get()->keyBy('id');

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'transfers' => $transfers,
            'memberships' => $memberships,
            'registrations' => $registrations,
        ]);
    }
}