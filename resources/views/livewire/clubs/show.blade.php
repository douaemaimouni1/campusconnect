<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            {{ $club->name }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        {{-- ================= HEADER DU CLUB ================= --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden mb-6">

            <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-500">
    @if ($club->banner)
        <img src="{{ asset('storage/' . $club->banner) }}" class="w-full h-full object-cover">
    @endif
</div>

            <div class="px-8 pb-8">

                <div class="flex flex-col md:flex-row md:items-end md:justify-between -mt-12">

                    <div class="flex items-end gap-5">

                        @if ($club->logo)
                            <img src="{{ asset('storage/' . $club->logo) }}"
                                 class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-lg bg-white">
                        @else
                            <div class="w-28 h-28 rounded-full bg-white border-4 border-white shadow-lg flex items-center justify-center text-4xl">
                                🏛
                            </div>
                        @endif

                        <div class="pb-2">
                            <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full mb-2">
                                {{ $club->category }}
                            </span>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $club->name }}
                            </h1>
                            <button
                                wire:click="toggleMembersModal"
                                class="text-sm text-gray-400 hover:text-indigo-600 hover:underline mt-1 transition">
                                {{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}
                            </button>
                        </div>

                    </div>
                    {{-- Bouton d'adhésion --}}
                    <div class="mt-6 md:mt-0 md:pb-2">

                        @if ($club->president_id === auth()->id())

                            <div class="flex items-center gap-3 flex-wrap">

                                <span class="inline-block bg-amber-50 text-amber-600 px-6 py-2 rounded-xl font-semibold">
                                    👑 Vous êtes le président
                                </span>

                                <a href="{{ route('clubs.requests', $club) }}"
                                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl font-semibold transition">
                                    📋 Gérer les demandes
                                </a>

                                <a href="{{ route('clubs.edit', $club) }}"
                                   class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-xl font-semibold transition">
                                    ✏️ Modifier
                                </a>

                            </div>

                        @elseif ($membershipStatus === 'accepted')

                            <div class="flex items-center gap-3">
                                <span class="inline-block bg-green-50 text-green-600 px-6 py-2 rounded-xl font-semibold">
                                    ✓ Membre
                                </span>

                                <button
                                    wire:click="leaveClub"
                                    wire:confirm="Quitter ce club ?"
                                    wire:loading.attr="disabled"
                                    class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                                    Quitter le club
                                </button>
                            </div>

                        @elseif ($membershipStatus === 'pending')

                            <button
                                wire:click="cancelMembership"
                                wire:loading.attr="disabled"
                                class="group bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                                <span class="group-hover:hidden">Demande envoyée</span>
                                <span class="hidden group-hover:inline">✕ Annuler</span>
                            </button>

                        @else

                            <button
                                wire:click="joinClub"
                                wire:loading.attr="disabled"
                                class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                                Rejoindre le club
                            </button>

                        @endif

                    </div>

                    @if (auth()->user()->role === 'superAdmin')
                        <div class="mt-3 md:mt-0 md:pb-2">
                            <button
                                wire:click="deleteClub"
                                wire:confirm="Supprimer définitivement ce club ? Cette action est irréversible."
                                wire:loading.attr="disabled"
                                class="bg-red-50 hover:bg-red-100 text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                                🗑️ Supprimer le club
                            </button>
                        </div>
                    @endif

                </div>

            </div>

        </div>

        {{-- ================= CARTE D'INFOS (façon LinkedIn "Overview") ================= --}}
        <div class="bg-white rounded-2xl shadow p-8 mb-6">

            <h3 class="text-lg font-bold mb-3">Aperçu</h3>
            <p class="text-gray-600 leading-relaxed mb-6">
                {{ $club->description }}
            </p>

            <div class="grid sm:grid-cols-2 gap-4 text-sm">

                <div class="flex items-center gap-3">
                    @if ($club->president->avatar)
                        <img src="{{ asset('storage/' . $club->president->avatar) }}"
                             class="w-9 h-9 rounded-full object-cover">
                    @else
                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center font-semibold text-indigo-600 text-xs">
                            {{ strtoupper(substr($club->president->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="text-gray-500">Président : <strong class="text-gray-800">{{ $club->president->name }}</strong></span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-lg">🏷️</span>
                    <span class="text-gray-500">Catégorie : <strong class="text-gray-800">{{ $club->category }}</strong></span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-lg">👥</span>
                    <span class="text-gray-500">{{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-lg">📅</span>
                    <span class="text-gray-500">Créé le <strong class="text-gray-800">{{ $club->created_at->translatedFormat('d F Y') }}</strong></span>
                </div>

            </div>

        </div>

        {{-- ================= FIL DE PUBLICATIONS (unique, mélangé) ================= --}}
        @if ($posts->isEmpty())

            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <p class="text-4xl mb-3">📝</p>
                <p class="text-gray-400">Aucune publication pour le moment.</p>
            </div>

        @else

            <div class="space-y-6">

                @foreach ($posts as $post)

                    <div wire:key="post-{{ $post->id }}" class="bg-white rounded-2xl shadow p-6">

                        <div class="flex items-center gap-3 mb-4">

                            @if ($post->user->avatar)
                                <img src="{{ asset('storage/' . $post->user->avatar) }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-semibold text-indigo-600">
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <p class="font-semibold">{{ $post->user->name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $post->created_at->diffForHumans() }}
                                </p>
                            </div>

                        </div>

                        <p class="text-gray-700 whitespace-pre-line">
                            {{ $post->content }}
                        </p>

                        @if ($post->image)
                            <img src="{{ asset('storage/' . $post->image) }}"
                                 class="w-full rounded-xl mt-4 max-h-96 object-cover">
                        @endif

                        {{-- Mini-carte événement, seulement si le post est lié à un événement --}}
                        @if ($post->event)

                            @php
                                $linkedEvent = $post->event;
                                $linkedStatus = $eventRegistrations[$linkedEvent->id] ?? null;
                            @endphp

                            <div class="mt-4 border border-gray-200 rounded-xl p-4">

                                @if ($linkedEvent->image)
                                    <img src="{{ asset('storage/' . $linkedEvent->image) }}"
                                         class="w-full h-40 object-cover rounded-lg mb-3">
                                @endif

                                <h4 class="font-bold">{{ $linkedEvent->title }}</h4>

                                <div class="text-sm text-gray-500 mt-2 space-y-1">
                                    <p>📅 {{ $linkedEvent->date->translatedFormat('d F Y à H:i') }}</p>
                                    <p>📍 {{ $linkedEvent->location }}</p>
                                    <p>{{ $linkedEvent->participants_count }} / {{ $linkedEvent->capacity }} participants</p>
                                </div>

                                <div class="mt-4">

                                    @if ($linkedStatus === 'confirmed')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-green-50 hover:bg-red-50 text-green-600 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">✓ Inscrit</span>
                                            <span class="hidden group-hover:inline">✕ Annuler</span>
                                        </button>

                                    @elseif ($linkedStatus === 'pending')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">Demande envoyée</span>
                                            <span class="hidden group-hover:inline">✕ Annuler</span>
                                        </button>

                                    @else

                                        <button
                                            wire:click="joinEvent({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
                                            Participer
                                        </button>

                                    @endif

                                </div>

                            </div>

                        @endif

                    </div>

                @endforeach

            </div>

        @endif

    </div>

    {{-- ================= MODALE : LISTE DES MEMBRES ================= --}}
    @if ($showMembersModal)

        <div
            wire:click.self="toggleMembersModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">

                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">
                        👥 Membres ({{ $members->count() }})
                    </h3>
                    <button
                        wire:click="toggleMembersModal"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                        ✕
                    </button>
                </div>

                <div class="overflow-y-auto p-5 space-y-3">

                    @forelse ($members as $membership)

                        <div wire:key="member-{{ $membership->id }}" class="flex items-center gap-3">

                            @if ($membership->user->avatar)
                                <img src="{{ asset('storage/' . $membership->user->avatar) }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-semibold text-indigo-600 text-sm">
                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $membership->user->name }}</p>
                                <p class="text-xs text-gray-400">
                                    Membre depuis {{ $membership->responded_at?->translatedFormat('d F Y') ?? $membership->created_at->translatedFormat('d F Y') }}
                                </p>
                            </div>

                        </div>

                    @empty

                        <p class="text-gray-400 text-sm text-center py-4">Aucun membre pour le moment.</p>

                    @endforelse

                </div>

            </div>

        </div>

    @endif

</div>