<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
        🛠️ Dashboard Super Admin
    </h2>

    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">👥 Utilisateurs</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">🏛 Clubs</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['clubs'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">📅 Événements</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['events'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-orange-500">
            <p class="text-sm text-gray-500">📳 Demandes d'adhésion en attente</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['pendingMemberships'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">⏳ Demandes de participation en attente</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['pendingEventRequests'] }}</p>
        </div>

    </div>

    <!-- Gestion des clubs -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">

        <div class="px-5 py-4 border-b">
            <h3 class="font-semibold text-lg text-gray-800">🏛 Gestion des clubs</h3>
        </div>

        <table class="w-full text-left">
            <thead class="bg-gray-50 text-sm text-gray-500">
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
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $club->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $club->category }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $club->president?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $club->members_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <button
                                wire:click="confirmClubDeletion({{ $club->id }})"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                🗑️ Supprimer
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-gray-400">
                            Aucun club pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $clubs->links() }}
        </div>

    </div>

    <!-- Gestion des utilisateurs -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">

        <div class="px-5 py-4 border-b">
            <h3 class="font-semibold text-lg text-gray-800">👥 Gestion des utilisateurs</h3>
        </div>

        <table class="w-full text-left">
            <thead class="bg-gray-50 text-sm text-gray-500">
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
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->department ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($user->isSuperAdmin())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    👑 Super Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Utilisateur
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
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
                        <td class="px-5 py-3 text-right">
                            @if ($user->id === auth()->id())
                                <span class="text-gray-400 text-sm">— c'est vous —</span>
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
                        <td colspan="6" class="px-5 py-6 text-center text-gray-400">
                            Aucun utilisateur pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $users->links() }}
        </div>

    </div>

    <!-- Modale de confirmation suppression club -->
    @if ($confirmingClubDeletion)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-club-{{ $confirmingClubDeletion }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">⚠️ Confirmer la suppression</h3>
                <p class="text-gray-600 text-sm mb-6">
                    Cette action est irréversible depuis l'interface : le club et
                    tous ses événements seront supprimés. Confirmes-tu ?
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelClubDeletion"
                        class="px-4 py-2 text-sm rounded-md border text-gray-600 hover:bg-gray-50">
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

    <!-- Modale de confirmation bannissement/réactivation simple -->
    @if ($confirmingUserBanToggle)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-user-{{ $confirmingUserBanToggle }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">⚠️ Confirmer l'action</h3>
                <p class="text-gray-600 text-sm mb-6">
                    Veux-tu vraiment changer le statut de ce compte utilisateur ?
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelUserBanToggle"
                        class="px-4 py-2 text-sm rounded-md border text-gray-600 hover:bg-gray-50">
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

    <!-- Modale : bannissement bloqué (au moins un club sans successeur éligible) -->
    @if ($blockedBanUserId)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-blocked-{{ $blockedBanUserId }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">🚫 Suspension impossible pour le moment</h3>
                <p class="text-gray-600 text-sm mb-3">
                    Cet utilisateur est président du/des club(s) suivant(s), qui n'ont
                    aucun autre membre éligible (adhésion acceptée + profil complété)
                    pour reprendre la présidence :
                </p>
                <ul class="list-disc list-inside text-sm text-gray-700 mb-4">
                    @foreach ($blockingClubs as $clubName)
                        <li>{{ $clubName }}</li>
                    @endforeach
                </ul>
                <p class="text-gray-600 text-sm mb-6">
                    Pour suspendre cet utilisateur, supprime d'abord le(s) club(s)
                    concerné(s) depuis la section « Gestion des clubs » ci-dessus.
                </p>
                <div class="flex justify-end">
                    <button
                        wire:click="cancelBlockedBan"
                        class="px-4 py-2 text-sm rounded-md border text-gray-600 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modale : sélection du/des successeur(s) avant bannissement -->
    @if ($selectingSuccessorUserId)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:key="modal-succession-{{ $selectingSuccessorUserId }}">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">👑 Choisir le(s) successeur(s)</h3>
                <p class="text-gray-600 text-sm mb-4">
                    Cet utilisateur est président du/des club(s) ci-dessous. Choisis un
                    successeur pour chacun : la suspension ne sera effective qu'une
                    fois que le(s) successeur(s) auront accepté.
                </p>

                <div class="space-y-4 mb-6">
                    @foreach ($clubsNeedingSuccessor as $clubId => $data)
                        <div class="border rounded-md p-3">
                            <p class="font-medium text-gray-800 text-sm mb-2">{{ $data['club_name'] }}</p>
                            <div class="space-y-1">
                                @foreach ($data['candidates'] as $candidate)
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
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

                <div class="flex justify-end gap-3">
                    <button
                        wire:click="cancelSuccessorSelection"
                        class="px-4 py-2 text-sm rounded-md border text-gray-600 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button
                        wire:click="submitSuccessorProposals"
                        class="px-4 py-2 text-sm rounded-md bg-purple-600 text-white hover:bg-purple-700">
                        Proposer le(s) transfert(s)
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>