<?php

namespace App\Livewire\Admin;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPresidencyTransfer;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\ClubPresidencyTransferProposed;
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

    // Id du club orphelin (president_id = NULL) pour lequel on propose un
    // nouveau président (null = aucune modale ouverte). Contrairement à
    // $clubsNeedingSuccessor/$clubsWithoutSuccessor, ce flux n'est PAS
    // restreint aux membres du club (voir eligiblePresidentCandidates()).
    public ?int $proposingPresidentForClub = null;

    // Texte tapé dans le champ de recherche de la modale de proposition de président.
    public string $presidentSearch = '';

    public function confirmClubDeletion(int $clubId): void
    {
        $this->confirmingClubDeletion = $clubId;
    }

    public function cancelClubDeletion(): void
    {
        $this->confirmingClubDeletion = null;
    }

    /**
     * Supprime le club en attente de confirmation.
     *
     * Utilise find() (et non findOrFail()) car cette méthode peut être
     * invoquée deux fois de suite si l'utilisateur double-clique avant que
     * l'interface n'ait fini de se mettre à jour (ce qui arrivait avec un
     * findOrFail(null) provoquant un 404 sur le 2e appel). On ignore alors
     * silencieusement l'appel en trop.
     */
    public function deleteClub(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        if (! $this->confirmingClubDeletion) {
            return;
        }

        $club = Club::find($this->confirmingClubDeletion);
        $this->confirmingClubDeletion = null;

        if (! $club) {
            return;
        }

        $club->events()->delete();
        $club->delete();

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
     *
     * Utilise find() (et non findOrFail()) pour tolérer un double-clic sur le
     * bouton "Confirmer" : le 2e appel, une fois $confirmingUserBanToggle
     * remis à null par le 1er, est simplement ignoré au lieu de provoquer un
     * 404 (ModelNotFoundException).
     */
    public function toggleUserBan(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        if (! $this->confirmingUserBanToggle) {
            return;
        }

        $user = User::find($this->confirmingUserBanToggle);
        $this->confirmingUserBanToggle = null;

        if (! $user) {
            return;
        }

        abort_if($user->id === auth()->id(), 403);

        // Sécurité en profondeur : on ne doit jamais arriver ici pour bannir
        // un président de club (l'interface ne devrait pas le permettre).
        abort_if(! $user->is_banned && $user->clubs()->exists(), 403);

        $user->is_banned = ! $user->is_banned;
        $user->save();

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
     *   de présidence (pending) et notifie le successeur choisi. Le club
     *   garde son president_id actuel (qui pointe vers l'utilisateur banni,
     *   donc plus personne ne peut le gérer) jusqu'à ce que le successeur
     *   accepte/refuse (voir Notifications\Bell) ou qu'un admin intervienne
     *   manuellement.
     * - Pour les clubs SANS successeur : passent directement à
     *   president_id = NULL, en attente d'intervention administrative.
     *
     * Utilise find() par cohérence avec deleteClub()/toggleUserBan() : un
     * double-clic ne doit jamais provoquer un 404.
     */
    public function submitUserBan(): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        if (! $this->selectingSuccessorUserId) {
            return;
        }

        $user = User::find($this->selectingSuccessorUserId);

        if (! $user) {
            $this->cancelSuccessorSelection();

            return;
        }

        abort_if($user->id === auth()->id(), 403);

        foreach ($this->clubsNeedingSuccessor as $clubId => $data) {
            $selectedId = $this->selectedSuccessors[$clubId] ?? null;

            $validIds = $data['candidates']->pluck('id')->all();

            abort_if(! $selectedId || ! in_array((int) $selectedId, $validIds, true), 422);

            $transfer = ClubPresidencyTransfer::create([
                'club_id' => $clubId,
                'current_president_id' => $user->id,
                'proposed_president_id' => $selectedId,
                'status' => 'pending',
                'initiated_by' => 'admin',
                'reason' => 'ban',
            ]);

            User::find($selectedId)->notify(new ClubPresidencyTransferProposed($transfer));
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

    /**
     * Ouvre la modale de proposition de président pour un club orphelin.
     *
     * Contrairement à confirmUserBanToggle() (succession volontaire), on ne
     * vérifie PAS que l'utilisateur est déjà membre du club : un club
     * orphelin peut n'avoir aucun membre du tout (voir eligiblePresidentCandidates()).
     */
    public function openPresidentProposal(int $clubId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $club = Club::find($clubId);

        if (! $club) {
            return;
        }

        // Sécurité en profondeur : le bouton ne doit apparaître côté vue que
        // pour un club sans président, mais on ne fait jamais confiance
        // uniquement à l'affichage.
        abort_if($club->president_id !== null, 403);

        // On évite d'ouvrir une deuxième proposition concurrente si une
        // proposition est déjà en attente de réponse pour ce club.
        $alreadyPending = ClubPresidencyTransfer::where('club_id', $clubId)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            session()->flash('success', "Une proposition de présidence est déjà en attente de réponse pour ce club.");

            return;
        }

        $this->proposingPresidentForClub = $clubId;
        $this->presidentSearch = '';
    }

    public function cancelPresidentProposal(): void
    {
        $this->proposingPresidentForClub = null;
        $this->presidentSearch = '';
    }

    /**
     * Envoie la proposition de présidence à l'utilisateur choisi.
     *
     * Le candidat n'est pas assigné immédiatement : il reçoit une
     * notification (réutilise ClubPresidencyTransferProposed) et doit
     * accepter depuis Notifications\Bell::respondToTransfer() pour devenir
     * effectivement président.
     *
     * Guards assouplis (find() au lieu de findOrFail(), sortie silencieuse
     * si $proposingPresidentForClub est déjà à null) pour éviter un 404 en
     * cas de double-clic, comme pour deleteClub()/toggleUserBan().
     */
    public function proposePresident(int $userId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        if (! $this->proposingPresidentForClub) {
            return;
        }

        $club = Club::find($this->proposingPresidentForClub);

        if (! $club) {
            $this->cancelPresidentProposal();

            return;
        }

        // Revérification : le club doit toujours être orphelin au moment du clic.
        abort_if($club->president_id !== null, 403);

        $alreadyPending = ClubPresidencyTransfer::where('club_id', $club->id)
            ->where('status', 'pending')
            ->exists();

        abort_if($alreadyPending, 409);

        $candidate = User::find($userId);

        if (! $candidate) {
            return;
        }

        abort_if($candidate->is_banned || ! $candidate->profile_completed, 422);

        $transfer = ClubPresidencyTransfer::create([
            'club_id' => $club->id,
            'current_president_id' => null,
            'proposed_president_id' => $candidate->id,
            'status' => 'pending',
            'initiated_by' => 'admin',
            'reason' => 'vacant',
        ]);

        $candidate->notify(new ClubPresidencyTransferProposed($transfer));

        $this->cancelPresidentProposal();

        session()->flash('success', "La proposition de présidence pour « {$club->name} » a été envoyée à {$candidate->name}.");
    }

    /**
     * Liste des candidats affichables dans la modale de proposition de
     * président : utilisateurs actifs (non bannis, profil complété), filtrés
     * par la recherche (nom ou email). Volontairement PAS restreint aux
     * membres du club (un club orphelin peut n'avoir aucun membre).
     */
    private function eligiblePresidentCandidates()
    {
        return User::where('is_banned', false)
            ->where('profile_completed', true)
            ->when($this->presidentSearch !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->presidentSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->presidentSearch . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
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

        $presidentCandidates = $this->proposingPresidentForClub
            ? $this->eligiblePresidentCandidates()
            : collect();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'clubs' => $clubs,
            'users' => $users,
            'presidentCandidates' => $presidentCandidates,
        ]);
    }
}