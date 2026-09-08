<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-serif font-semibold text-2xl text-ink flex items-center gap-2">
            <x-lucide-calendar class="w-6 h-6 text-pine-600" />
            Événements
        </h2>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        <div class="flex flex-col md:flex-row gap-4 mb-10">

            <div class="relative flex-1">
                <x-lucide-search class="w-4 h-4 text-muted absolute left-4 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Rechercher un événement..."
                    class="w-full pl-11 rounded-xl border-gray-300 shadow-sm focus:ring-pine-500 focus:border-pine-500">
            </div>

            <select
                wire:model.live="category"
                class="rounded-xl border-gray-300 shadow-sm focus:ring-pine-500 focus:border-pine-500 md:w-64">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

        </div>

        @if ($events->isEmpty())

            <div class="bg-paper rounded-xl shadow p-12 text-center text-muted">
                Aucun événement à venir ne correspond à votre recherche.
            </div>

        @else

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($events as $event)

                    <div wire:key="event-{{ $event->id }}"
                         class="bg-white rounded-xl shadow overflow-hidden hover:shadow-xl transition duration-300 border-l-4 border-l-pine-500">

                        @if ($event->image)
                           <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}" class="w-full h-44 object-cover">
                        @endif

                        <div class="p-5">

                            <span class="inline-block bg-pine-50 text-pine-600 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                {{ $event->club->category }}
                            </span>

                            <p class="text-sm text-muted font-semibold">
                                {{ $event->club->name }}
                            </p>

                            <h3 class="font-serif text-xl font-bold mt-1 text-ink">
                                {{ $event->title }}
                            </h3>

                            <p class="text-muted text-sm mt-2 flex items-center gap-1.5">
                                <x-lucide-calendar-days class="w-4 h-4" />
                                {{ $event->date->translatedFormat('d F Y à H:i') }}
                            </p>

                            <p class="text-muted text-sm flex items-center gap-1.5">
                                <x-lucide-map-pin class="w-4 h-4" />
                                {{ $event->location }}
                            </p>

                            <p class="text-muted text-sm mt-2">
                                {{ $event->participants_count }} / {{ $event->capacity }} participants
                            </p>

                            <div class="mt-5">
                                <a href="{{ route('events.show', $event) }}"
                                   class="flex items-center justify-center gap-2 w-full bg-pine-600 hover:bg-pine-700 text-white py-2 rounded-lg font-semibold transition">
                                    <x-lucide-eye class="w-4 h-4" />
                                    Voir
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