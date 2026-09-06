<div wire:poll.visible.15s>

    <div class="max-w-3xl mx-auto py-8 px-6">

        @if (session()->has('error'))
            <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="bg-pine-50 text-pine-700 p-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- ================= HEADER DU CLUB ================= --}}
        <div class="bg-white rounded-2xl shadow mb-6">

            <div class="h-40 bg-pine-700 rounded-t-2xl overflow-hidden">
                @if ($club->banner)
                    <img src="{{ asset('storage/' . $club->banner) }}" class="w-full h-full object-cover">
                @endif
            </div>

            <div class="px-8 pb-8">

                {{-- Avatar + nom : chevauchent volontairement la bannière (marge négative isolée) --}}
                 <div class="-mt-12 mb-6 flex flex-col sm:flex-row sm:items-end gap-4">
                    @if ($club->logo)
                        <img src="{{ asset('storage/' . $club->logo) }}"
                             class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-lg bg-white shrink-0">
                    @else
                        <div class="w-28 h-28 rounded-full bg-white border-4 border-white shadow-lg flex items-center justify-center shrink-0">
                            <x-lucide-landmark class="w-10 h-10 text-pine-600" />
                        </div>
                    @endif

                    <h1 class="font-serif font-bold text-3xl text-ink pb-2 mt-3 sm:mt-0">
                        {{ $club->name }}
                    </h1>
                </div>

                {{-- Boutons d'action : toujours dans la zone blanche, jamais sur la bannière --}}
                <div class="flex justify-end">

                                       @if ($club->president_id === auth()->id())

                        <div class="grid grid-cols-2 gap-3 w-full max-w-lg">

                            <span class="inline-flex items-center justify-center gap-2 bg-amber-50 text-amber-600 px-4 py-2 rounded-xl font-semibold">
                                <x-lucide-crown class="w-4 h-4" />
                                Vous êtes le président
                            </span>

                            <a href="{{ route('clubs.requests', $club) }}"
                               class="inline-flex items-center justify-center gap-2 bg-pine-600 hover:bg-pine-700 text-white px-4 py-2 rounded-xl font-semibold transition">
                                <x-lucide-clipboard-list class="w-4 h-4" />
                                Gérer les demandes
                            </a>

                            <a href="{{ route('clubs.edit', $club) }}"
                               class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-ink px-4 py-2 rounded-xl font-semibold transition">
                                <x-lucide-pencil class="w-4 h-4" />
                                Modifier
                            </a>

                            <button
                                wire:click="togglePostModal"
                                class="inline-flex items-center justify-center gap-2 bg-pine-600 hover:bg-pine-700 text-white px-4 py-2 rounded-xl font-semibold transition">
                                <x-lucide-square-pen class="w-4 h-4" />
                                Créer un post
                            </button>

                        </div>

                    @elseif ($membershipStatus === 'accepted')

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-2 bg-pine-50 text-pine-600 px-6 py-2 rounded-xl font-semibold">
                                <x-lucide-check class="w-4 h-4" />
                                Membre
                            </span>

                            <button
                                wire:click="leaveClub"
                                wire:confirm="Quitter ce club ?"
                                wire:loading.attr="disabled"
                                class="bg-gray-100 hover:bg-red-50 text-muted hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                                Quitter le club
                            </button>
                        </div>

                    @elseif ($membershipStatus === 'pending')

                        <button
                            wire:click="cancelMembership"
                            wire:loading.attr="disabled"
                            class="group bg-gray-100 hover:bg-red-50 text-muted hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                            <span class="group-hover:hidden">Demande envoyée</span>
                            <span class="hidden group-hover:inline">Annuler</span>
                        </button>

                    @else

                        <button
                            wire:click="joinClub"
                            wire:loading.attr="disabled"
                            class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                            Rejoindre le club
                        </button>

                    @endif

                </div>

            </div>

        </div>

        {{-- ================= CARTE D'INFOS (façon LinkedIn "Overview") ================= --}}
        <div class="bg-white rounded-2xl shadow p-8 mb-6">

            <h3 class="font-serif text-lg font-bold text-ink mb-3">Aperçu</h3>
            <p class="text-muted leading-relaxed mb-6">
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
                            <div class="w-9 h-9 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600 text-xs">
                                {{ strtoupper(substr($club->president->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-muted">Président : <strong class="text-ink hover:underline">{{ $club->president->name }}</strong></span>
                    </a>

                @else

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-muted text-xs">
                            ?
                        </div>
                        <span class="text-muted italic">Aucun président actuellement</span>
                    </div>

                @endif

                <div class="flex items-center gap-3">
                    <x-lucide-tag class="w-4 h-4 text-pine-600" />
                    <span class="text-muted">Catégorie : <strong class="text-ink">{{ $club->category }}</strong></span>
                </div>

                <button
                    wire:click="toggleMembersModal"
                    class="flex items-center gap-3 hover:text-pine-600 transition text-left">
                    <x-lucide-users class="w-4 h-4 text-pine-600" />
                    <span class="text-muted hover:underline">{{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                </button>

                <div class="flex items-center gap-3">
                    <x-lucide-calendar class="w-4 h-4 text-pine-600" />
                    <span class="text-muted">Créé le <strong class="text-ink">{{ $club->created_at->translatedFormat('d F Y') }}</strong></span>
                </div>

            </div>

        </div>

        {{-- ================= FIL DE PUBLICATIONS (unique, mélangé) ================= --}}
        @if ($posts->isEmpty())

            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <x-lucide-inbox class="w-10 h-10 text-muted mx-auto mb-3" />
                <p class="text-muted">Aucune publication pour le moment.</p>
            </div>

        @else

            <div class="space-y-6">

                @foreach ($posts as $post)

                    <div wire:key="post-{{ $post->id }}" class="bg-white rounded-2xl shadow p-6 border-l-4 border-l-pine-500">

                        <div class="flex items-center justify-between mb-4">

                            <div class="flex items-center gap-3">

                                @if ($club->logo)
                                    <img src="{{ asset('storage/' . $club->logo) }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-pine-100 flex items-center justify-center">
                                        <x-lucide-landmark class="w-5 h-5 text-pine-600" />
                                    </div>
                                @endif

                                <div>
                                    <p class="font-semibold text-ink">{{ $club->name }}</p>
                                    <p class="text-xs text-muted">
                                        {{ $post->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>

                            {{-- Menu "⋮" façon Instagram, réservé au président. --}}
                            @if ($club->president_id === auth()->id())

                                <div x-data="{ open: false }" class="relative">

                                    <button
                                        @click="open = ! open"
                                        class="text-muted hover:text-ink px-2">
                                        <x-lucide-more-vertical class="w-5 h-5" />
                                    </button>

                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-cloak
                                        class="absolute right-0 mt-1 w-40 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-10">

                                        <button
                                            @click="open = false"
                                            wire:click="openEditPostModal({{ $post->id }})"
                                            class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-ink hover:bg-gray-50">
                                            <x-lucide-pencil class="w-4 h-4" />
                                            Modifier
                                        </button>

                                        @if ($post->event)

                                            <button
                                                wire:click="deleteEvent({{ $post->event->id }})"
                                                wire:confirm="Supprimer cet événement et ce post ? Cette action est définitive."
                                                class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                                Supprimer
                                            </button>

                                        @else

                                            <button
                                                wire:click="deletePost({{ $post->id }})"
                                                wire:confirm="Supprimer ce post ? Cette action est définitive."
                                                class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                                Supprimer
                                            </button>

                                        @endif

                                    </div>

                                </div>

                            @endif

                        </div>

                        <p class="text-ink whitespace-pre-line">
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

                            <div class="mt-4 border border-pine-100 rounded-xl p-4">

                                @if ($linkedEvent->image)
                                    <img src="{{ asset('storage/' . $linkedEvent->image) }}"
                                         class="w-full h-40 object-cover rounded-lg mb-3">
                                @endif

                                <h4 class="font-serif font-bold text-ink">{{ $linkedEvent->title }}</h4>

                                @if ($linkedEvent->description)
                                    <p class="text-sm text-muted mt-1 leading-relaxed">
                                        {{ $linkedEvent->description }}
                                    </p>
                                @endif

                                <div class="text-sm text-muted mt-2 space-y-1">
                                    <p class="flex items-center gap-1.5">
                                        <x-lucide-calendar-days class="w-4 h-4" />
                                        {{ $linkedEvent->date->translatedFormat('d F Y à H:i') }}
                                    </p>
                                    <p class="flex items-center gap-1.5">
                                        <x-lucide-map-pin class="w-4 h-4" />
                                        {{ $linkedEvent->location }}
                                    </p>

                                    @if ($club->president_id === auth()->id())
                                        <button
                                            type="button"
                                            wire:click="openEventParticipantsModal({{ $linkedEvent->id }})"
                                            class="flex items-center gap-1.5 hover:text-pine-600 hover:underline transition">
                                            <x-lucide-users class="w-4 h-4" />
                                            {{ $linkedEvent->participants_count }} / {{ $linkedEvent->capacity }} participants
                                        </button>
                                    @else
                                        <p>{{ $linkedEvent->participants_count }} / {{ $linkedEvent->capacity }} participants</p>
                                    @endif
                                </div>

                                <div class="mt-4">

                                    @if ($club->president_id === auth()->id())

                                        <span class="inline-flex items-center justify-center gap-2 w-full bg-amber-50 text-amber-600 py-2 rounded-lg font-semibold">
                                            <x-lucide-crown class="w-4 h-4" />
                                            Vous êtes l'organisateur
                                        </span>

                                    @elseif ($linkedStatus === 'confirmed')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-pine-50 hover:bg-red-50 text-pine-600 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">Inscrit</span>
                                            <span class="hidden group-hover:inline">Annuler</span>
                                        </button>

                                    @elseif ($linkedStatus === 'pending')

                                        <button
                                            wire:click="cancelEventRegistration({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-gray-100 hover:bg-red-50 text-muted hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">Demande envoyée</span>
                                            <span class="hidden group-hover:inline">Annuler</span>
                                        </button>

                                    @elseif ($linkedEventIsFull)

                                        <span class="inline-block w-full text-center bg-gray-100 text-muted py-2 rounded-lg font-semibold">
                                            Complet
                                        </span>

                                    @else

                                        <button
                                            wire:click="joinEvent({{ $linkedEvent->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-full bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
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
                    <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2">
                        <x-lucide-users class="w-5 h-5 text-pine-600" />
                        Membres ({{ $members->count() }})
                    </h3>
                    <button
                        wire:click="toggleMembersModal"
                        class="text-muted hover:text-ink">
                        <x-lucide-x class="w-5 h-5" />
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
                                    <div class="w-10 h-10 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600 text-sm">
                                        {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="font-semibold text-ink text-sm">{{ $membership->user->name }}</p>
                                    <p class="text-xs text-muted">
                                        Membre depuis {{ $membership->responded_at?->translatedFormat('d F Y') ?? $membership->created_at->translatedFormat('d F Y') }}
                                    </p>
                                </div>

                            </a>

                            @if ($club->president_id === auth()->id())
                                <button
                                    wire:click="removeMember({{ $membership->id }})"
                                    wire:confirm="Retirer {{ $membership->user->name }} du club ?"
                                    wire:loading.attr="disabled"
                                    class="text-xs text-muted hover:text-red-600 font-semibold shrink-0 transition">
                                    Retirer
                                </button>
                            @endif

                        </div>

                    @empty

                        <p class="text-muted text-sm text-center py-4">Aucun membre pour le moment.</p>

                    @endforelse

                </div>

            </div>

        </div>

    @endif

    {{-- ================= MODALE : LISTE DES PARTICIPANTS À UN ÉVÉNEMENT ================= --}}
    @if ($showEventParticipantsModal)

        <div
            wire:click.self="closeEventParticipantsModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">

                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2">
                        <x-lucide-users class="w-5 h-5 text-pine-600" />
                        Participants ({{ $eventParticipants->count() }})
                    </h3>
                    <button
                        wire:click="closeEventParticipantsModal"
                        type="button"
                        class="text-muted hover:text-ink">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <div class="overflow-y-auto p-5 space-y-3">

                    @forelse ($eventParticipants as $registration)

                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600 text-sm">
                                {{ strtoupper(substr($registration->user->name ?? '?', 0, 1)) }}
                            </div>
                            <p class="font-semibold text-ink text-sm">
                                {{ $registration->user->name ?? 'Utilisateur supprimé' }}
                            </p>
                        </div>

                    @empty

                        <p class="text-muted text-sm text-center py-4">Aucun participant confirmé pour le moment.</p>

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
                    <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2">
                        @if ($editingPost)
                            <x-lucide-pencil class="w-5 h-5 text-pine-600" />
                            Modifier
                        @else
                            <x-lucide-square-pen class="w-5 h-5 text-pine-600" />
                            Créer un post
                        @endif
                    </h3>
                    <button
                        wire:click="togglePostModal"
                        class="text-muted hover:text-ink">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit="savePost" class="overflow-y-auto p-5 space-y-4">

                    {{-- Choix du type, uniquement à la création --}}
                    @unless ($editingPost)
                        <div class="flex gap-2 mb-2">
                            <button
                                type="button"
                                wire:click="$set('postType', 'post')"
                                class="flex items-center justify-center gap-2 flex-1 py-2 rounded-lg text-sm font-semibold transition {{ $postType === 'post' ? 'bg-pine-600 text-white' : 'bg-gray-100 text-muted' }}">
                                <x-lucide-square-pen class="w-4 h-4" />
                                Post simple
                            </button>
                            <button
                                type="button"
                                wire:click="$set('postType', 'event')"
                                class="flex items-center justify-center gap-2 flex-1 py-2 rounded-lg text-sm font-semibold transition {{ $postType === 'event' ? 'bg-pine-600 text-white' : 'bg-gray-100 text-muted' }}">
                                <x-lucide-calendar class="w-4 h-4" />
                                Événement
                            </button>
                        </div>
                    @endunless

                    @if ($postType === 'post')

                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Contenu</label>
                            <textarea
                                wire:model="postContent"
                                rows="4"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none"
                                placeholder="Quoi de neuf dans votre club ?"></textarea>
                            @error('postContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink mb-2">Image (optionnelle)</label>

                            @if ($postImage)
                                <img src="{{ $postImage->temporaryUrl() }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @elseif ($existingPostImageUrl)
                                <img src="{{ $existingPostImageUrl }}" class="w-full h-40 object-cover rounded-lg mb-2">
                            @endif

                            <input type="file" wire:model="postImage" accept="image/*" class="text-sm">
                            @error('postImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    @else

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Titre</label>
                                <input
                                    type="text"
                                    wire:model="eventTitle"
                                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                                @error('eventTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Lieu</label>
                                <input
                                    type="text"
                                    wire:model="eventLocation"
                                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                                @error('eventLocation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Description</label>
                            <textarea
                                wire:model="eventDescription"
                                rows="2"
                                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none"></textarea>
                            @error('eventDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Date et heure</label>
                                <input
                                    type="datetime-local"
                                    wire:model="eventDate"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                                @error('eventDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Capacité</label>
                                <input
                                    type="number"
                                    min="1"
                                    wire:model="eventCapacity"
                                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                                @error('eventCapacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Image (optionnelle)</label>

                            @if ($eventImage)
                                <img src="{{ $eventImage->temporaryUrl() }}" class="w-full h-24 object-cover rounded-lg mb-1">
                            @elseif ($existingEventImageUrl)
                                <img src="{{ $existingEventImageUrl }}" class="w-full h-24 object-cover rounded-lg mb-1">
                            @endif

                            <input type="file" wire:model="eventImage" accept="image/*" class="text-sm">
                            @error('eventImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    @endif

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
                        {{ $editingPost ? 'Enregistrer les modifications' : 'Publier' }}
                    </button>

                </form>

            </div>

        </div>

    @endif

</div>