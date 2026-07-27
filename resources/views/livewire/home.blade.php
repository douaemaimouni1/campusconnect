<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            🎓 CampusConnect
        </h2>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        {{-- Barre de recherche en live --}}
        <div class="mb-8">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="🔍 Rechercher un club ou un événement..."
                class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Événements populaires --}}
        <h2 class="text-2xl font-bold mb-6">
            🔥 Événements populaires
        </h2>

        @if ($this->popularEvents->isEmpty())

            <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400 mb-10">
                Aucun événement à venir pour le moment.
            </div>

        @else

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">

                @foreach ($this->popularEvents as $event)

                    @php
                        $colors = ['blue', 'green', 'purple'];
                        $color = $colors[$loop->index % 3];
                    @endphp

                    <div wire:key="event-{{ $event->id }}"
                         class="bg-white rounded-xl shadow overflow-hidden hover:shadow-xl duration-300">

                        <img
                            src="{{ $event->image ? asset('storage/' . $event->image) : 'https://picsum.photos/500/300?' . $event->id }}"
                            class="w-full h-52 object-cover">

                        <div class="p-5">

                            <p class="text-{{ $color }}-600 font-semibold">
                                {{ $event->club->name }}
                            </p>

                            <h3 class="text-2xl font-bold mt-2">
                                {{ $event->title }}
                            </h3>

                            <p class="text-gray-600 mt-3">
                                📅 {{ $event->date->translatedFormat('d F Y') }}
                            </p>

                            <p class="text-gray-600">
                                📍 {{ $event->location }}
                            </p>

                            <p class="text-gray-400 text-sm mt-2">
                                {{ $event->participants_count }} / {{ $event->capacity }} participants
                            </p>

                            {{-- La page détail événement n'existe pas encore --}}
                            <button disabled
                                class="mt-5 w-full bg-{{ $color }}-300 text-white py-2 rounded-lg cursor-not-allowed">
                                Voir les détails
                            </button>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

        {{-- Clubs populaires --}}
        <div class="mt-12">

            <h2 class="text-2xl font-bold mb-5">
                🏆 Clubs populaires
            </h2>

            @if ($this->popularClubs->isEmpty())

                <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
                    Aucun club trouvé.
                </div>

            @else

                <div class="bg-white rounded-xl shadow p-6">

                    <ul class="space-y-1">

                        @foreach ($this->popularClubs as $club)

                            <li wire:key="club-{{ $club->id }}">

                                <a href="{{ route('clubs.show', $club) }}"
                                   class="flex items-center justify-between p-3 -mx-3 rounded-lg hover:bg-gray-50 transition">

                                    <div class="flex items-center gap-3">
                                        @if ($club->logo)
                                            <img src="{{ asset('storage/' . $club->logo) }}"
                                                 class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <span class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-sm">
                                                🏛
                                            </span>
                                        @endif
                                        <span class="text-gray-800">{{ $club->name }}</span>
                                    </div>

                                    <span class="text-sm text-gray-400">
                                        {{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}
                                    </span>

                                </a>

                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif

        </div>

    </div>

</div>