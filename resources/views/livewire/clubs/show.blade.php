<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            {{ $club->name }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- ================= HEADER DU CLUB ================= --}}
        <div class="bg-white rounded-2xl shadow mb-6">

            <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-t-2xl overflow-hidden">
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
                                🏠
                            </div>
                        @endif

                        <div class="pb-2">
                            <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full mb-2">
                                {{ $club->category }}
                            </span>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $club->name }}
                            </h1>
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

                                <button
                                    wire:click="togglePostModal"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl font-semibold transition">
                                    📝 Créer un post
                                </button>

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
                                <span class="hidden group-hover:inline">✗ Annuler</span>
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

                                @if ($club->president)

                    <a href="{{ route('profile.show', $club->president) }}"
                       class="flex items-center gap-3 hover:opacity-80 transition">
                        @if ($club->president->avatar)
                            <img src="{{ asset('storage/' . $club->president->avatar) }}"
                                 class="w-9 h-9 rounded-full object-cover">
                        @else
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center font-semibold text-indigo-600 text-xs">
                                {{ strtoupper(substr($club->president->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-gray-500">Président : <strong class="text-gray-800 hover:underline">{{ $club->president->name }}</strong></span>
                    </a>

                @else

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-xs">
                            ?
                        </div>
                        <span class="text-gray-400 italic">Aucun président actuellement</span>
                    </div>

                @endif

                <div class="flex items-center gap-3">
                    <span class="text-lg">🏷️</span>
                    <span class="text-gray-500">Catégorie : <strong class="text-gray-800">{{ $club->category }}</strong></span>
                </div>

                <button
                    wire:click="toggleMembersModal"
                    class="flex items-center gap-3 hover:text-indigo-600 transition text-left">
                    <span class="text-lg">👥</span>
                    <span class="text-gray-500 hover:underline">{{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                </button>

                <div class="flex items-center gap-3">
                    <span class="text-lg">📅</span>
                    <span class="text-gray-500">Créé le <strong class="text-gray-800">{{ $club->created_at->translatedFormat('d F Y') }}</strong></span>
                </div>

            </div>

        </div>

        {{-- ================= FIL DE PUBLICATIONS (unique, mélangé) ================= --}}
        @if ($posts->isEmpty())

            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <p class="text-4xl mb-3">📭</p>
                <p class="text-gray-400">Aucune publication pour le moment.</p>
            </div>

        @else

            <div class="space-y-6">

                @foreach ($posts as $post)

                    <div wire:key="post-{{ $post->id }}" class="bg-white rounded-2xl shadow p-6">

                        <div class="flex items-center justify-between mb-4">

                            <div class="flex items-center gap-3">

                                @if ($club->logo)
                                    <img src="{{ asset('storage/' . $club->logo) }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-lg">
                                        🏠
                                    </div>
                                @endif

                                <div>
                                    <p class="font-semibold">{{ $club->name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $post->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>

                            {{-- Menu "⋮" façon Instagram, réservé au président. --}}
                            @if ($club->president_id === auth()->id())

                                <div x-data="{ open: false }" class="relative">

                                    <button
                                        @click="open = ! open"
                                        class="text-gray-400 hover:text-gray-600 text-xl leading-none px-2">
                                        ⋮
                                    </button>

                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-cloak
                                        class="absolute right-0 mt-1 w-40 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-10">

                                        <button
                                            @click="open = false"
                                            wire:click="openEditPostModal({{ $post->id }})"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            ✏️ Modifier
                                        </button>

                                        @if ($post->event)

                                            <button
                                                wire:click="deleteEvent({{ $post->event->id }})"
                                                wire:confirm="Supprimer cet événement et ce post ? Cette action est définitive."
                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                🗑️ Supprimer
                                            </button>

                                        @else

                                            <button
                                                wire:click="deletePost({{ $post->id }})"
                                                wire:confirm="Supprimer ce post ? Cette action est définitive."
                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                🗑️ Supprimer
                                            </button>

                                        @endif

                                    </div>

                                </div>

                            @endif

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
                                $linkedEventIsFull = $linkedEvent->participants_count >= $linkedEvent->capacity;
                            @endphp

                            <div class="mt-4 border border-gray-200 rounded-xl p-4">

                                @if ($linkedEvent->image)
                                    <img src="{{ asset('storage/' . $linkedEvent->image) }}"
                                         class="w-full h-40 object-cover rounded-lg mb-3">
                                @endif

                                <h4 class="font-bold">{{ $linkedEvent->title }}</h4>

                                @if ($linkedEvent->description)
                                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                                        {{ $linkedEvent->description }}
                                    </p>
                                @endif

                                <div class="text-sm text-gray-500 mt-2 space-y-1">
                                    <p>📅 {{ $linkedEvent->date->translatedFormat('d F Y à H:i') }}</p>
                                    <p>📍 {{ $linkedEvent->location }}</p>
                                    <p>{{ $linkedEvent->participants_count }} / {{ $linkedEvent->capacity }} participants</p>
                                </div>

                                <div class="mt-4">

                                    @if ($club->president_id === auth()->id())

                                        <span class="inline-block w-full text-center bg-amber-50 text-amber-600 py-2 rounded-lg font-semibold">
                                            👑 Vous êtes l'organisateur
                                        </span>

                                    @elseif ($linkedStatus === 'confirmed')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-green-50 hover:bg-red-50 text-green-600 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">✓ Inscrit</span>
                                            <span class="hidden group-hover:inline">✗ Annuler</span>
                                        </button>

                                    @elseif ($linkedStatus === 'pending')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">Demande envoyée</span>
                                            <span class="hidden group-hover:inline">✗ Annuler</span>
                                        </button>

                                    @elseif ($linkedEventIsFull)

                                        <span class="inline-block w-full text-center bg-gray-100 text-gray-400 py-2 rounded-lg font-semibold">
                                            Complet
                                        </span>

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

                        <div wire:key="member-{{ $membership->id }}" class="flex items-center justify-between gap-3">

                            <a href="{{ route('profile.show', $membership->user) }}"
                               class="flex items-center gap-3 hover:opacity-80 transition">

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

                            </a>

                            @if ($club->president_id === auth()->id())
                                <button
                                    wire:click="removeMember({{ $membership->id }})"
                                    wire:confirm="Retirer {{ $membership->user->name }} du club ?"
                                    wire:loading.attr="disabled"
                                    class="text-xs text-gray-400 hover:text-red-600 font-semibold shrink-0 transition">
                                    Retirer
                                </button>
                            @endif

                        </div>

                    @empty

                        <p class="text-gray-400 text-sm text-center py-4">Aucun membre pour le moment.</p>

                    @endforelse

                </div>

            </div>

        </div>

    @endif

    {{-- ================= MODALE : CRÉER / MODIFIER UN POST (SIMPLE OU ÉVÉNEMENT) ================= --}}
    @if ($showPostModal)

        <div
            wire:click.self="togglePostModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">

                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $editingPost ? '✏️ Modifier' : '📝 Créer un post' }}
                    </h3>
                    <button
                        wire:click="togglePostModal"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                        ✕
                    </button>
                </div>

                <form wire:submit="savePost" class="overflow-y-auto p-5 space-y-4">

                    {{-- Choix du type, uniquement à la création --}}
                    @unless ($editingPost)
                        <div class="flex gap-2 mb-2">
                            <button
                                type="button"
                                wire:click="$set('postType', 'post')"
                                class="flex-1 py-2 rounded-lg text-sm font-semibold transition {{ $postType === 'post' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                📝 Post simple
                            </button>
                            <button
                                type="button"
                                wire:click="$set('postType', 'event')"
                                class="flex-1 py-2 rounded-lg text-sm font-semibold transition {{ $postType === 'event' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                📅 Événement
                            </button>
                        </div>
                    @endunless

                    @if ($postType === 'post')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                            <textarea
                                wire:model="postContent"
                                rows="4"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                placeholder="Quoi de neuf dans votre club ?"></textarea>
                            @error('postContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image (optionnelle)</label>

                            @if ($postImage)
                                <img src="{{ $postImage->temporaryUrl() }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @elseif ($existingPostImageUrl)
                                <img src="{{ $existingPostImageUrl }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @endif

                            <input type="file" wire:model="postImage" accept="image/*" class="text-sm">
                            @error('postImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    @else

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                            <input
                                type="text"
                                wire:model="eventTitle"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('eventTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                wire:model="eventDescription"
                                rows="3"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                            @error('eventDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                            <input
                                type="text"
                                wire:model="eventLocation"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('eventLocation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date et heure</label>
                            <input
                                type="datetime-local"
                                wire:model="eventDate"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('eventDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacité</label>
                            <input
                                type="number"
                                min="1"
                                wire:model="eventCapacity"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('eventCapacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image (optionnelle)</label>

                            @if ($eventImage)
                                <img src="{{ $eventImage->temporaryUrl() }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @elseif ($existingEventImageUrl)
                                <img src="{{ $existingEventImageUrl }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @endif

                            <input type="file" wire:model="eventImage" accept="image/*" class="text-sm">
                            @error('eventImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    @endif

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
                        {{ $editingPost ? 'Enregistrer les modifications' : 'Publier' }}
                    </button>

                </form>

            </div>

        </div>

    @endif

</div>