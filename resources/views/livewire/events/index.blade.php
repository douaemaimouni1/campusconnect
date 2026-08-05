<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            📅 Événements
        </h2>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        <div class="flex flex-col md:flex-row gap-4 mb-10">

            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="🔍 Rechercher un événement..."
                class="flex-1 rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">

            <select
                wire:model.live="category"
                class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 md:w-64">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

        </div>

        @if ($events->isEmpty())

            <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
                Aucun événement à venir ne correspond à votre recherche.
            </div>

        @else

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($events as $event)

                    <div wire:key="event-{{ $event->id }}"
                         class="bg-white rounded-xl shadow overflow-hidden hover:shadow-xl transition duration-300">

                        @if ($event->image)
                            <img src="{{ asset('storage/' . $event->image) }}" class="w-full h-44 object-cover">
                        @endif

                        <div class="p-5">

                            <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                {{ $event->club->category }}
                            </span>

                            <p class="text-sm text-gray-500 font-semibold">
                                {{ $event->club->name }}
                            </p>

                            <h3 class="text-xl font-bold mt-1">
                                {{ $event->title }}
                            </h3>

                            <p class="text-gray-500 text-sm mt-2">
                                📅 {{ $event->date->translatedFormat('d F Y à H:i') }}
                            </p>

                            <p class="text-gray-500 text-sm">
                                📍 {{ $event->location }}
                            </p>

                            <p class="text-gray-400 text-sm mt-2">
                                {{ $event->participants_count }} / {{ $event->capacity }} participants
                            </p>

                            <div class="mt-5">
                                <a href="{{ route('events.show', $event) }}"
                                   class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold transition">
                                    👁 Voir
                                </a>
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            <div class="mt-10">
                {{ $events->links() }}
            </div>

        @endif

    </div>

</div>