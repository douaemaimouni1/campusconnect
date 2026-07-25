<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800">
            {{ $club->name }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-6">

        {{-- ================= HEADER DU CLUB ================= --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden mb-10">

            <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

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
                        </div>

                    </div>

                    {{-- Bouton d'adhésion --}}
                    <div class="mt-6 md:mt-0 md:pb-2">

                        @if ($membershipStatus === 'accepted')

                            <span class="inline-block bg-green-50 text-green-600 px-6 py-2 rounded-xl font-semibold">
                                ✓ Membre
                            </span>

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

                </div>

                <p class="text-gray-600 mt-6 max-w-2xl">
                    {{ $club->description }}
                </p>

                <div class="flex items-center gap-6 mt-5 text-sm text-gray-500">
                    <span>👤 Président : <strong>{{ $club->president->name }}</strong></span>
                    <span>👥 {{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                </div>

            </div>

        </div>

        {{-- ================= ÉVÉNEMENTS DU CLUB ================= --}}
        <div class="mb-10">

            <h2 class="text-xl font-bold mb-5">📅 Événements du club</h2>

            @if ($events->isEmpty())

                <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
                    Aucun événement pour le moment.
                </div>

            @else

                <div class="grid md:grid-cols-2 gap-6">

                    @foreach ($events as $event)

                        @php
                            $eventStatus = $eventRegistrations[$event->id] ?? null;
                        @endphp

                        <div wire:key="event-{{ $event->id }}"
                             class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition">

                            @if ($event->image)
                                <img src="{{ asset('storage/' . $event->image) }}"
                                     class="w-full h-40 object-cover">
                            @endif

                            <div class="p-5">

                                <h3 class="text-lg font-bold">{{ $event->title }}</h3>

                                <p class="text-gray-500 text-sm mt-1 line-clamp-2">
                                    {{ $event->description }}
                                </p>

                                <div class="text-sm text-gray-500 mt-3 space-y-1">
                                    <p>📅 {{ $event->date->translatedFormat('d F Y à H:i') }}</p>
                                    <p>📍 {{ $event->location }}</p>
                                    <p>{{ $event->participants_count }} / {{ $event->capacity }} participants</p>
                                </div>

                                <div class="mt-4">

                                    @if ($eventStatus === 'confirmed')

                                        <button
                                            wire:click="cancelEventRegistration({{ $event->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-green-50 hover:bg-red-50 text-green-600 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">✓ Inscrit</span>
                                            <span class="hidden group-hover:inline">✕ Annuler</span>
                                        </button>

                                    @elseif ($eventStatus === 'pending')

                                        <button
                                            wire:click="cancelEventRegistration({{ $event->id }})"
                                            wire:loading.attr="disabled"
                                            class="group w-full bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 py-2 rounded-lg font-semibold transition">
                                            <span class="group-hover:hidden">Demande envoyée</span>
                                            <span class="hidden group-hover:inline">✕ Annuler</span>
                                        </button>

                                    @else

                                        <button
                                            wire:click="joinEvent({{ $event->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
                                            Participer
                                        </button>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

        {{-- ================= GALERIE PHOTOS ================= --}}
        <div class="mb-10">

            <h2 class="text-xl font-bold mb-5">🖼 Galerie</h2>

            @if ($gallery->isEmpty())

                <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
                    Aucune photo pour le moment.
                </div>

            @else

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                    @foreach ($gallery as $photo)

                        <img wire:key="photo-{{ $photo->id }}"
                             src="{{ asset('storage/' . $photo->image) }}"
                             class="w-full h-32 object-cover rounded-xl shadow hover:opacity-90 transition cursor-pointer">

                    @endforeach

                </div>

            @endif

        </div>

        {{-- ================= PUBLICATIONS ================= --}}
        <div>

            <h2 class="text-xl font-bold mb-5">📝 Publications</h2>

            @if ($posts->isEmpty())

                <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
                    Aucune publication pour le moment.
                </div>

            @else

                <div class="space-y-6">

                    @foreach ($posts as $post)

                        <div wire:key="post-{{ $post->id }}" class="bg-white rounded-xl shadow p-6">

                            {{-- Auteur --}}
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

                            {{-- Contenu texte --}}
                            <p class="text-gray-700 whitespace-pre-line">
                                {{ $post->content }}
                            </p>

                            {{-- Image du post (si présente) --}}
                            @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}"
                                     class="w-full rounded-xl mt-4 max-h-96 object-cover">
                            @endif

                            {{-- Mini-carte événement lié (si présent) --}}
                            @if ($post->event)

                                @php
                                    $linkedEvent = $post->event;
                                    $linkedStatus = $eventRegistrations[$linkedEvent->id] ?? null;
                                @endphp

                                <div class="mt-4 border border-gray-200 rounded-xl p-4">

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

    </div>

</x-app-layout>