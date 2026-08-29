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
    // (utilisé pour la réactivation, ou le bannissement d'un utilisateur qui n'est
    // président d'aucun club).
    public ?int $confirmingUserBanToggle = null;

    // Id de l'utilisateur pour lequel on doit gérer la succession de ses club(s)
    // avant de le bannir. Le bannissement est TOUJOURS exécuté immédiatement au
    // moment de la validation, quel que soit l'état des clubs (voir submitUserBan()).
    public ?int $selectingSuccessorUserId = null;

    // Clubs présidés par l'utilisateur AVEC au moins un successeur éligible.
    // [club_id => ['club_name' => string, 'candidates' => Collection<User>]]
    public array $clubsNeedingSuccessor = [];

    // Clubs présidés par l'utilisateur SANS aucun successeur éligible.
    // Purement informatif dans la modale : ces clubs passeront automatiquement
    // à president_id = NULL au moment du bannissement (voir submitUserBan()).
    // [club_id => club_name]
    public array $clubsWithoutSuccessor = [];

    // [club_id => id du candidat choisi] (uniquement pour les clubs de $clubsNeedingSuccessor)
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
     * Point d'entrée du bouton "Suspendre" / "Réactiver".
     *
     * Règle : le bannissement n'est JAMAIS bloqué par l'état des clubs de
     * l'utilisateur. Si l'utilisateur est président d'au moins un club, on
     * ouvre une modale récapitulative (choix des successeurs pour les clubs
     * qui en ont, information pour ceux qui n'en ont pas), mais la validation
     * de cette modale bannit l'utilisateur immédiatement dans tous les cas.
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

        $clubsAsPresident = $user->clubs()->get();

        // Pas président d'un club : bannissement simple, comme avant.
        if ($clubsAsPresident->isEmpty()) {
            $this->confirmingUserBanToggle = $userId;

            return;
        }

        $needingSuccessor = [];
        $withoutSuccessor = [];

        foreach ($clubsAsPresident as $club) {
            $candidates = $this->eligibleSuccessors($club, $user);

            if ($candidates->isEmpty()) {
                $withoutSuccessor[$club->id] = $club->name;
            } else {
                $needingSuccessor[$club->id] = [
                    'club_name' => $club->name,
                    'candidates' => $candidates,
                ];
            }
        }

        $this->selectingSuccessorUserId = $userId;
        $this->clubsNeedingSuccessor = $needingSuccessor;
        $this->clubsWithoutSuccessor = $withoutSuccessor;
        $this->selectedSuccessors = [];
    }

    public function cancelUserBanToggle(): void
    {
        $this->confirmingUserBanToggle = null;
    }

    public function cancelSuccessorSelection(): void
    {
        $this->selectingSuccessorUserId = null;
        $this->clubsNeedingSuccessor = [];
        $this->clubsWithoutSuccessor = [];
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
     * Bannit immédiatement l'utilisateur, quel que soit l'état de ses clubs :
     * - Pour les clubs AVEC successeur choisi : crée une demande de transfert
     *   de présidence (pending). Le club garde son president_id actuel (qui
     *   pointe vers l'utilisateur banni, donc plus personne ne peut le gérer)
     *   jusqu'à ce que le successeur accepte (Étape D, à venir) ou qu'un admin
     *   intervienne manuellement.
     * - Pour les clubs SANS successeur : passent directement à
     *   president_id = NULL, en attente d'intervention administrative.
     */
    public function submitUserBan(): void
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

        foreach (array_keys($this->clubsWithoutSuccessor) as $clubId) {
            Club::where('id', $clubId)->update(['president_id' => null]);
        }

        $user->is_banned = true;
        $user->save();

        $clubNames = collect($this->clubsNeedingSuccessor)->pluck('club_name')
            ->merge(collect($this->clubsWithoutSuccessor)->values())
            ->join(', ');

        $this->cancelSuccessorSelection();

        session()->flash(
            'success',
            "L'utilisateur « {$user->name} » a été suspendu. "
                . ($clubNames
                    ? "Club(s) concerné(s) : {$clubNames}. Les clubs sans successeur disponible sont désormais sans président et nécessitent une intervention administrative."
                    : '')
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