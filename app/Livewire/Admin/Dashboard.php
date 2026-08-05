<?php

namespace App\Livewire\Admin;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPresidencyTransfer;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    // Id du club en attente de confirmation de suppression (null = aucune modale ouverte)
    public ?int $confirmingClubDeletion = null;

    // Id de l'utilisateur en attente de confirmation simple de bannissement/réactivation
    // (utilisé uniquement pour la réactivation, ou le bannissement d'un utilisateur
    // qui n'est président d'aucun club).
    public ?int $confirmingUserBanToggle = null;

    // Id de l'utilisateur dont le bannissement est bloqué car au moins un de ses
    // clubs n'a aucun successeur éligible.
    public ?int $blockedBanUserId = null;

    // Noms des clubs qui bloquent le bannissement (aucun successeur éligible).
    public array $blockingClubs = [];

    // Id de l'utilisateur pour lequel on doit choisir un ou plusieurs successeurs
    // avant de pouvoir le bannir.
    public ?int $selectingSuccessorUserId = null;

    // [club_id => ['club_name' => string, 'candidates' => Collection<User>]]
    public array $clubsNeedingSuccessor = [];

    // [club_id => id du candidat choisi]
    public array $selectedSuccessors = [];

    public function confirmClubDeletion(int $clubId): void
    {
        $this->confirmingClubDeletion = $clubId;
    }

    public function cancelClubDeletion(): void
    {
        $this->confirmingClubDeletion = null;
    }

    public function deleteClub(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $club = Club::findOrFail($this->confirmingClubDeletion);

        $club->events()->delete();
        $club->delete();

        $this->confirmingClubDeletion = null;

        session()->flash('success', "Le club « {$club->name} » et ses événements ont été supprimés.");
    }

    /**
     * Point d'entrée du bouton "Suspendre" / "Réactiver". Décide quel
     * scénario s'applique : réactivation simple, bannissement simple,
     * bannissement bloqué, ou bannissement nécessitant un transfert
     * de présidence.
     */
    public function confirmUserBanToggle(int $userId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($userId);

        abort_if($user->id === auth()->id(), 403);

        // Réactivation : pas de vérification de présidence nécessaire.
        if ($user->is_banned) {
            $this->confirmingUserBanToggle = $userId;

            return;
        }

        // Bannissement : on vérifie d'abord si l'utilisateur est président
        // d'un ou plusieurs clubs (soft-deleted exclus automatiquement).
        $clubsAsPresident = $user->clubs()->get();

        if ($clubsAsPresident->isEmpty()) {
            $this->confirmingUserBanToggle = $userId;

            return;
        }

        $blocking = [];
        $needingSuccessor = [];

        foreach ($clubsAsPresident as $club) {
            $candidates = $this->eligibleSuccessors($club, $user);

            if ($candidates->isEmpty()) {
                $blocking[] = $club->name;
            } else {
                $needingSuccessor[$club->id] = [
                    'club_name' => $club->name,
                    'candidates' => $candidates,
                ];
            }
        }

        if (! empty($blocking)) {
            $this->blockedBanUserId = $userId;
            $this->blockingClubs = $blocking;

            return;
        }

        $this->selectingSuccessorUserId = $userId;
        $this->clubsNeedingSuccessor = $needingSuccessor;
        $this->selectedSuccessors = [];
    }

    public function cancelUserBanToggle(): void
    {
        $this->confirmingUserBanToggle = null;
    }

    public function cancelBlockedBan(): void
    {
        $this->blockedBanUserId = null;
        $this->blockingClubs = [];
    }

    public function cancelSuccessorSelection(): void
    {
        $this->selectingSuccessorUserId = null;
        $this->clubsNeedingSuccessor = [];
        $this->selectedSuccessors = [];
    }

    /**
     * Bannissement/réactivation "simple" : soit une réactivation, soit le
     * bannissement d'un utilisateur qui n'est président d'aucun club.
     */
    public function toggleUserBan(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($this->confirmingUserBanToggle);

        abort_if($user->id === auth()->id(), 403);

        // Sécurité en profondeur : on ne doit jamais arriver ici pour bannir
        // un président de club (l'interface ne devrait pas le permettre).
        abort_if(! $user->is_banned && $user->clubs()->exists(), 403);

        $user->is_banned = ! $user->is_banned;
        $user->save();

        $this->confirmingUserBanToggle = null;

        session()->flash(
            'success',
            $user->is_banned
                ? "L'utilisateur « {$user->name} » a été suspendu."
                : "L'utilisateur « {$user->name} » a été réactivé."
        );
    }

    /**
     * Envoie une proposition de transfert de présidence pour chaque club
     * concerné. Le bannissement effectif n'est PAS appliqué ici : il ne
     * le sera qu'une fois tous les transferts acceptés par les candidats
     * (logique à venir dans l'interface d'acceptation du successeur).
     */
    public function submitSuccessorProposals(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($this->selectingSuccessorUserId);

        abort_if($user->id === auth()->id(), 403);

        foreach ($this->clubsNeedingSuccessor as $clubId => $data) {
            $selectedId = $this->selectedSuccessors[$clubId] ?? null;

            $validIds = $data['candidates']->pluck('id')->all();

            abort_if(! $selectedId || ! in_array((int) $selectedId, $validIds, true), 422);

            ClubPresidencyTransfer::create([
                'club_id' => $clubId,
                'current_president_id' => $user->id,
                'proposed_president_id' => $selectedId,
                'status' => 'pending',
                'initiated_by' => 'admin',
                'reason' => 'ban',
            ]);
        }

        $clubNames = collect($this->clubsNeedingSuccessor)->pluck('club_name')->join(', ');

        $this->cancelSuccessorSelection();

        session()->flash(
            'success',
            "Proposition(s) de transfert de présidence envoyée(s) pour : {$clubNames}. "
                . "L'utilisateur « {$user->name} » sera suspendu automatiquement dès que le(s) "
                . 'candidat(s) auront accepté.'
        );
    }

    /**
     * Retourne les membres éligibles pour devenir président d'un club :
     * adhésion acceptée, profil complété, compte non banni, hors président actuel.
     */
    private function eligibleSuccessors(Club $club, User $president)
    {
        return User::whereHas('clubMemberships', function ($query) use ($club) {
                $query->where('club_id', $club->id)
                    ->where('status', 'accepted');
            })
            ->where('id', '!=', $president->id)
            ->where('profile_completed', true)
            ->where('is_banned', false)
            ->get();
    }

    public function render()
    {
        $stats = [
            'users' => User::count(),
            'clubs' => Club::count(),
            'events' => Event::count(),
            'eventRegistrations' => EventRegistration::count(),
            'pendingMemberships' => ClubMembership::where('status', 'pending')->count(),
            'pendingEventRequests' => EventRegistration::where('status', 'pending')->count(),
        ];

        $clubs = Club::with('president')
            ->withCount(['memberships as members_count' => function ($query) {
                $query->where('status', 'accepted');
            }])
            ->latest()
            ->paginate(10, ['*'], 'clubsPage');

        $users = User::latest()
            ->paginate(10, ['*'], 'usersPage');

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'clubs' => $clubs,
            'users' => $users,
        ]);
    }
}