<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-ink">
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
            @if ($event->image)
               <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}" class="w-full h-64 object-contain bg-pine-700">
            @endif
            <div class="p-8">
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <a href="{{ route('clubs.show', $event->club) }}"
                           class="text-pine-600 font-semibold text-sm hover:underline">
                            {{ $event->club->name }}
                        </a>
                        <h1 class="text-2xl font-bold text-ink mt-1">
                            {{ $event->title }}
                        </h1>
                    </div>

                    {{-- Menu "⋮" — Modifier/Supprimer, réservé à l'organisateur de cet événement --}}
                    @if ($isOrganizer)

                        <div x-data="{ open: false }" class="relative shrink-0">

                            <button
                                @click="open = ! open"
                                class="text-muted-500 hover:text-ink px-2">
                                <x-lucide-more-vertical class="w-5 h-5" />
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-cloak
                                class="absolute right-0 mt-1 w-40 bg-white rounded-xl shadow-lg border border-muted-200 py-1 z-10">

                                
                                    href="{{ route('clubs.show', $event->club) }}?edit_post={{ $relatedPostId }}"
                                    class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-ink hover:bg-pine-50">
                                    <x-lucide-pencil class="w-4 h-4" />
                                    Modifier
                                </a>

                                <button
                                    wire:click="deleteEvent"
                                    wire:confirm="Supprimer cet événement et le post associé ? Cette action est définitive."
                                    class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                    Supprimer
                                </button>

                            </div>

                        </div>

                    @endif

                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-muted-500 mb-6 mt-4">
                    <span class="flex items-center gap-1.5">
                        <x-lucide-calendar class="w-4 h-4" />
                        {{ $event->date->translatedFormat('d F Y à H:i') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <x-lucide-map-pin class="w-4 h-4" />
                        {{ $event->location }}
                    </span>

                    @if ($isOrganizer)
                        <button
                            wire:click="openParticipantsModal"
                            type="button"
                            class="flex items-center gap-1.5 hover:text-pine-600 hover:underline transition">
                            <x-lucide-users class="w-4 h-4" />
                            {{ $event->participants_count }} / {{ $event->capacity }} participants
                        </button>
                    @else
                        <span class="flex items-center gap-1.5">
                            <x-lucide-users class="w-4 h-4" />
                            {{ $event->participants_count }} / {{ $event->capacity }} participants
                        </span>
                    @endif
                </div>
                <p class="text-ink-700 leading-relaxed whitespace-pre-line mb-6">
                    {{ $event->description }}
                </p>
                @if ($isOrganizer)
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-6 py-2 rounded-xl font-semibold">
                        <x-lucide-crown class="w-4 h-4" />
                        Vous êtes l'organisateur
                    </span>
                @elseif ($registrationStatus === 'confirmed')
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 px-6 py-2 rounded-xl font-semibold">
                            <x-lucide-check class="w-4 h-4" />
                            Inscrit
                        </span>
                        <button
                            wire:click="cancelRegistration"
                            wire:confirm="Annuler votre inscription ?"
                            wire:loading.attr="disabled"
                            class="bg-muted-100 hover:bg-red-50 text-muted-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                            Annuler
                        </button>
                    </div>
                @elseif ($registrationStatus === 'pending')
                    <button
                        wire:click="cancelRegistration"
                        wire:loading.attr="disabled"
                        class="group bg-muted-100 hover:bg-red-50 text-muted-500 hover:text-red-600 px-6 py-2 rounded-xl font-semibold transition">
                        <span class="group-hover:hidden">Demande envoyée</span>
                        <span class="hidden group-hover:inline-flex items-center gap-1.5">
                            <x-lucide-x class="w-4 h-4" />
                            Annuler
                        </span>
                    </button>
                @elseif ($event->participants_count >= $event->capacity)
                    <span class="inline-block bg-muted-100 text-muted-500 px-6 py-2 rounded-xl font-semibold">
                        Complet
                    </span>
                @else
                    <button
                        wire:click="joinEvent"
                        wire:loading.attr="disabled"
                        class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                        Participer
                    </button>
                @endif
            </div>
        </div>

        {{-- Modal liste des participants (organisateur uniquement) --}}
        @if ($isOrganizer && $showParticipantsModal)
            <div class="fixed inset-0 bg-ink/50 flex items-center justify-center z-50 px-4"
                 wire:click.self="closeParticipantsModal">
                <div class="bg-white rounded-2xl shadow-lg max-w-md w-full max-h-[80vh] flex flex-col">

                    <div class="flex items-center justify-between p-6 border-b border-muted-200">
                        <h3 class="font-serif text-lg font-bold text-ink">
                            Participants ({{ $participants->count() }})
                        </h3>
                        <button wire:click="closeParticipantsModal" type="button" class="text-muted-500 hover:text-ink">
                            <x-lucide-x class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="overflow-y-auto p-4 space-y-2">
                        @forelse ($participants as $registration)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-muted-50">
                                <div class="w-9 h-9 rounded-full bg-pine-50 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-semibold text-pine-700">
                                        {{ strtoupper(substr($registration->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                                              <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink truncate">
                                        {{ $registration->user->name ?? 'Utilisateur supprimé' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-muted-500 text-center py-6">
                                Aucun participant confirmé pour le moment.
                            </p>
                        @endforelse
                    </div>

                </div>
            </div>
        @endif

    </div>
</div>