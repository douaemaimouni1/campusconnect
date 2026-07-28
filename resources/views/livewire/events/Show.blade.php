<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            {{ $event->title }}
        </h2>
    </div>
    <div class="max-w-3xl mx-auto py-8 px-6">

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
            <img
                src="{{ $event->image ? asset('storage/' . $event->image) : 'https://picsum.photos/800/400?' . $event->id }}"
                class="w-full h-64 object-cover">
            <div class="p-8">
                <a href="{{ route('clubs.show', $event->club) }}"
                   class="text-indigo-600 font-semibold text-sm hover:underline">
                    {{ $event->club->name }}
                </a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1 mb-4">
                    {{ $event->title }}
                </h1>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-gray-500 mb-6">
                    <span>📅 {{ $event->date->translatedFormat('d F Y à H:i') }}</span>
                    <span>📍 {{ $event->location }}</span>
                    <span>👥 {{ $event->participants_count }} / {{ $event->capacity }} participants</span>
                </div>
                <p class="text-gray-700 leading-relaxed whitespace-pre-line mb-6">
                    {{ $event->description }}
                </p>
                @if ($isOrganizer)
                    <span class="inline-block bg-amber-50 text-amber-600 px-6 py-2 rounded-xl font-semibold">
                        👑 Vous êtes l'organisateur
                    </span>
                @elseif ($registrationStatus === 'confirmed')
                    <div class="flex items-center gap-3">
                        <span class="inline-block bg-green-50 text-green-600 px-6 py-2 rounded-xl font-semibold">
                            ✓ Inscrit
                        </span>
                        <button
                            wire:click="cancelRegistration"
                            wire:confirm="Annuler votre inscription ?"
                            wire:loading.attr="disabled"
                            class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                            Annuler
                        </button>
                    </div>
                @elseif ($registrationStatus === 'pending')
                    <button
                        wire:click="cancelRegistration"
                        wire:loading.attr="disabled"
                        class="group bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                        <span class="group-hover:hidden">Demande envoyée</span>
                        <span class="hidden group-hover:inline">✗ Annuler</span>
                    </button>
                @elseif ($event->participants_count >= $event->capacity)
                    <span class="inline-block bg-gray-100 text-gray-400 px-6 py-2 rounded-xl font-semibold">
                        Complet
                    </span>
                @else
                    <button
                        wire:click="joinEvent"
                        wire:loading.attr="disabled"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                        Participer
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>