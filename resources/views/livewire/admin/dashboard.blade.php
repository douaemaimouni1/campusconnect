<div wire:poll.visible.15s>
    <h2 class="font-semibold text-xl text-ink leading-tight mb-6">
        🛠️ Dashboard Super Admin
    </h2>

    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-pine-500">
            <p class="text-sm text-muted-500">👥 Utilisateurs</p>
            <p class="text-3xl font-bold text-ink">{{ $stats['users'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-pine-600">
            <p class="text-sm text-muted-500">🏛 Clubs</p>
            <p class="text-3xl font-bold text-ink">{{ $stats['clubs'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-pine-400">
            <p class="text-sm text-muted-500">📅 Événements</p>
            <p class="text-3xl font-bold text-ink">{{ $stats['events'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-pine-700">
            <p class="text-sm text-muted-500">📳 Demandes d'adhésion en attente</p>
            <p class="text-3xl font-bold text-ink">{{ $stats['pendingMemberships'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-pine-800">
            <p class="text-sm text-muted-500">⏳ Demandes de participation en attente</p>
            <p class="text-3xl font-bold text-ink">{{ $stats['pendingEventRequests'] }}</p>
        </div>

    </div>

    <!-- Gestion des clubs -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">

        <div class="px-5 py-4 border-b">
            <h3 class="font-semibold text-lg text-ink">🏛 Gestion des clubs</h3>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-left min-w-[640px]">
            <thead class="bg-gray-50 text-sm text-muted-500">
                <tr>
                    <th class="px-5 py-3">Nom</th>
                    <th class="px-5 py-3">Catégorie</th>
                    <th class="px-5 py-3">Président</th>
                    <th class="px-5 py-3">Membres</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($clubs as $club)
                    <tr wire:key="club-{{ $club->id }}">
                        <td class="px-5 py-3 font-medium text-ink whitespace-nowrap">{{ $club->name }}</td>
                        <td class="px-5 py-3 text-muted-500 whitespace-nowrap">{{ $club->category }}</td>
                        <td class="px-5 py-3 text-muted-500 whitespace-nowrap">
                            @if ($club->president)
                                {{ $club->president->name }}
                            @else
                                <span class="text-muted-500">—</span>
                                <button
                                    wire:click="openPresidentProposal({{ $club->id }})"
                                    class="ml-2 text-pine-600 hover:text-pine-800 text-xs font-medium">
                                    👑 Proposer un président
                                </button>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-muted-500 whitespace-nowrap">{{ $club->members_count }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <button
                                wire:click="confirmClubDeletion({{ $club->id }})"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                🗑️ Supprimer
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-muted-500">
                            Aucun club pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="px-5 py-4">
            {{ $clubs->links() }}
        </div>

    </div>

    <!-- Gestion des utilisateurs -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">

        <div class="px-5 py-4 border-b">
            <h3 class="font-semibold text-lg text-ink">👥 Gestion des utilisateurs</h3>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-left min-w-[720px]">
            <thead class="bg-gray-50 text-sm text-muted-500">
                <tr>
                    <th class="px-5 py-3">Nom</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Département</th>
                    <th class="px-5 py-3">Rôle</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-5 py-3 font-medium text-ink whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-muted-500 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-muted-500 whitespace-nowrap">{{ $user->department ?? '—' }}</td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if ($user->isSuperAdmin())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pine-100 text-pine-700">
                                    👑 Super Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-muted-500">
                                    Utilisateur
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if ($user->is_banned)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    ⛔ Suspendu
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ✅ Actif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            @if ($user->id === auth()->id())
                                <span class="text-muted-500 text-sm">— c'est vous —</span>
                            @elseif ($user->is_banned)
                                <button
                                    wire:click="confirmUserBanToggle({{ $user->id }})"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    ✅ Réactiver
                                </button>
                            @else
                                <button
                                    wire:click="confirmUserBanToggle({{ $user->id }})"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    ⛔ Suspendre
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-6 text-center text-muted-500">
                            Aucun utilisateur pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="px-5 py-4">
            {{ $users->links() }}
        </div>

    </div>

    <!-- Modale de confirmation suppression club -->
    @if ($confirmingClubDeletion)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-club-{{ $confirmingClubDeletion }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-ink mb-2">⚠️ Confirmer la suppression</h3>
                <p class="text-muted-500 text-sm mb-6">
                    Cette action est irréversible depuis l'interface : le club et
                    tous ses événements seront supprimés. Confirmes-tu ?
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelClubDeletion"
                        class="px-4 py-2 text-sm rounded-md border text-muted-500 hover:bg-pine-50">
                        Annuler
                    </button>
                    <button
                        wire:click="deleteClub"
                        class="px-4 py-2 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">
                        🗑️ Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modale de confirmation bannissement/réactivation simple (pas président) -->
    @if ($confirmingUserBanToggle)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-user-{{ $confirmingUserBanToggle }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-ink mb-2">⚠️ Confirmer l'action</h3>
                <p class="text-muted-500 text-sm mb-6">
                    Veux-tu vraiment changer le statut de ce compte utilisateur ?
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelUserBanToggle"
                        class="px-4 py-2 text-sm rounded-md border text-muted-500 hover:bg-pine-50">
                        Annuler
                    </button>
                    <button
                        wire:click="toggleUserBan"
                        class="px-4 py-2 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modale : suspension d'un président de club (successeurs à choisir / clubs sans successeur) -->
    @if ($selectingSuccessorUserId)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-succession-{{ $selectingSuccessorUserId }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-ink mb-2">👑 Suspension d'un président de club</h3>
                <p class="text-muted-500 text-sm mb-4">
                    Cet utilisateur sera <strong>suspendu immédiatement</strong> après
                    validation. Pour les clubs ci-dessous où un successeur est
                    disponible, une demande de transfert de présidence lui sera
                    envoyée (le club reste actif en attendant sa réponse). Pour les
                    clubs sans successeur disponible, le club sera immédiatement sans
                    président et nécessitera une intervention administrative.
                </p>

                @if (count($clubsNeedingSuccessor) > 0)
                    <p class="text-sm font-medium text-ink mb-2">
                        Clubs avec successeur à choisir :
                    </p>
                    <div class="space-y-4 mb-6">
                        @foreach ($clubsNeedingSuccessor as $clubId => $data)
                            <div class="border rounded-md p-3">
                                <p class="font-medium text-ink text-sm mb-2">{{ $data['club_name'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($data['candidates'] as $candidate)
                                        <label class="flex items-center gap-2 text-sm text-ink">
                                            <input
                                                type="radio"
                                                wire:model="selectedSuccessors.{{ $clubId }}"
                                                value="{{ $candidate->id }}"
                                            >
                                            {{ $candidate->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (count($clubsWithoutSuccessor) > 0)
                    <p class="text-sm font-medium text-ink mb-2">
                        Clubs sans successeur disponible (passeront sans président) :
                    </p>
                    <div class="mb-6">
                        <ul class="list-disc list-inside text-sm text-muted-500 bg-gray-50 border rounded-md p-3">
                            @foreach ($clubsWithoutSuccessor as $clubName)
                                <li>{{ $clubName }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelSuccessorSelection"
                        class="px-4 py-2 text-sm rounded-md border text-muted-500 hover:bg-pine-50">
                        Annuler
                    </button>
                    <button
                        wire:click="submitUserBan"
                        class="px-4 py-2 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">
                        ⛔ Confirmer la suspension
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modale : proposition de président pour un club orphelin -->
    @if ($proposingPresidentForClub)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-propose-president-{{ $proposingPresidentForClub }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-ink mb-2">👑 Proposer un président</h3>
                <p class="text-muted-500 text-sm mb-4">
                    Choisis un utilisateur actif de la plateforme (n'importe lequel,
                    pas forcément membre du club). Il recevra une notification et
                    devra <strong>accepter</strong> pour devenir président.
                </p>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="presidentSearch"
                    placeholder="Rechercher par nom ou email..."
                    class="w-full border-gray-300 rounded-md text-sm mb-4 focus:border-pine-500 focus:ring-pine-500"
                >

                <div class="space-y-1 mb-6">
                    @forelse ($presidentCandidates as $candidate)
                        <div class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-gray-50" wire:key="candidate-{{ $candidate->id }}">
                            <div>
                                <p class="text-sm font-medium text-ink">{{ $candidate->name }}</p>
                                <p class="text-xs text-muted-500">{{ $candidate->email }}</p>
                            </div>
                            <button
                                wire:click="proposePresident({{ $candidate->id }})"
                                class="text-pine-600 hover:text-pine-800 text-xs font-medium">
                                Proposer
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-muted-500 text-center py-4">
                            Aucun utilisateur trouvé.
                        </p>
                    @endforelse
                </div>

                <div class="flex justify-end">
                    <button
                        wire:click="cancelPresidentProposal"
                        class="px-4 py-2 text-sm rounded-md border text-muted-500 hover:bg-pine-50">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>