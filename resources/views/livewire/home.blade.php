<div>

    {{-- ================= HERO SECTION ================= --}}
    <div class="relative bg-pine-700 overflow-hidden">

        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="max-w-7xl mx-auto px-6 py-14 relative">

            <h1 class="text-3xl sm:text-4xl font-bold text-white">
                Bienvenue sur CampusConnect
            </h1>

            <p class="text-amber-300 font-semibold text-lg mt-2">
                Tout ce qui fait vivre ton campus, au même endroit.
            </p>

            <p class="text-pine-50 mt-4 max-w-md">
                Découvre les clubs, les événements et les publications de ta communauté étudiante.
            </p>

            <div class="flex flex-wrap items-center gap-4 mt-7">

                <<a href="{{ route('clubs.index', ['create' => 1]) }}"
                   class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-ink font-semibold px-6 py-2.5 rounded-xl transition">
                    <x-lucide-plus-circle class="w-4 h-4" />
                    Créer un club
                </a>

                <span class="flex items-center gap-1.5 text-pine-50 text-sm">
                    <x-lucide-users class="w-4 h-4" />
                    {{ $this->popularClubs->count() }} clubs actifs
                </span>

                <span class="text-pine-200">•</span>

                <span class="flex items-center gap-1.5 text-pine-50 text-sm">
                    <x-lucide-calendar class="w-4 h-4" />
                    {{ $this->popularEvents->count() }} événements à venir
                </span>

            </div>

        </div>

    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        {{-- Barre de recherche en live --}}
        <div class="mb-8 relative">
            <x-lucide-search class="w-4 h-4 text-muted absolute left-4 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Rechercher un club ou un événement..."
                class="w-full rounded-xl border-ink/15 shadow-sm pl-11 focus:ring-pine-500 focus:border-pine-500">
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-start">

            {{-- ================= FIL DE POSTS ================= --}}
            <div class="lg:col-span-2 space-y-6">

                <h2 class="font-serif text-xl font-bold text-ink flex items-center gap-2 mb-2">
                    <x-lucide-newspaper class="w-5 h-5 text-pine-600" />
                    Fil d'actualité
                </h2>

                @if ($this->feedPosts->isEmpty())

                    <div class="bg-white rounded-2xl border border-ink/10 p-8 text-center text-muted">
                        Aucune publication pour le moment.
                    </div>

                @else

                    @foreach ($this->feedPosts as $post)

                        <div wire:key="post-{{ $post->id }}" class="bg-white rounded-2xl border border-ink/10 border-l-[3px] border-l-pine-500 p-6">

                            {{-- Auteur --}}
                            <div class="flex items-center gap-3 mb-4">

                                @if ($post->user->avatar)
                                    <img src="{{ asset('storage/' . $post->user->avatar) }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-pine-50 flex items-center justify-center font-serif font-semibold text-pine-600">
                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="font-semibold text-ink flex items-center gap-1.5">
                                        {{ $post->club->name }}
                                        <span class="text-muted text-xs font-normal">· {{ $post->user->name }}</span>
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{ $post->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>

                            {{-- Contenu texte --}}
                            <p class="text-ink whitespace-pre-line">
                                {{ $post->content }}
                            </p>

                            {{-- Image du post --}}
                            @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}"
                                     class="w-full rounded-xl mt-4 max-h-96 object-cover">
                            @endif

                            {{-- Mini-carte événement lié --}}
                            @if ($post->event)

                                <div class="mt-4 border border-ink/10 rounded-xl p-4">

                                    <h4 class="font-serif font-bold text-ink">{{ $post->event->title }}</h4>

                                    <div class="text-sm text-muted mt-2 space-y-1">
                                        <p class="flex items-center gap-1.5">
                                            <x-lucide-calendar class="w-4 h-4" />
                                            {{ $post->event->date->translatedFormat('d F Y à H:i') }}
                                        </p>
                                        <p class="flex items-center gap-1.5">
                                            <x-lucide-map-pin class="w-4 h-4" />
                                            {{ $post->event->location }}
                                        </p>
                                        <p>{{ $post->event->participants_count }} / {{ $post->event->capacity }} participants</p>
                                    </div>

                                    <a href="{{ route('events.show', $post->event) }}"
                                       class="mt-4 block text-center w-full bg-pine-600 hover:bg-pine-700 text-white py-2 rounded-lg transition">
                                        Voir l'événement
                                    </a>

                                </div>

                            @endif

                        </div>

                    @endforeach

                @endif

            </div>

            {{-- ================= COLONNE LATÉRALE ================= --}}
            <div class="space-y-6">

                {{-- Cette semaine (événements populaires) --}}
                <div class="bg-white rounded-2xl border border-ink/10 p-5">

                    <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2 mb-4">
                        <x-lucide-flame class="w-4 h-4 text-amber-500" />
                        Cette semaine
                    </h3>

                    @if ($this->popularEvents->isEmpty())

                        <p class="text-sm text-muted text-center py-4">
                            Aucun événement à venir.
                        </p>

                    @else

                        <div class="space-y-3">

                            @foreach ($this->popularEvents->take(4) as $event)

                                <a href="{{ route('events.show', $event) }}"
                                   wire:key="side-event-{{ $event->id }}"
                                   class="flex items-start gap-3 -mx-2 p-2 rounded-lg hover:bg-pine-50 transition">

                                    <div class="w-11 h-11 shrink-0 rounded-lg bg-pine-50 flex flex-col items-center justify-center text-pine-700">
                                        <span class="text-sm font-bold leading-none">{{ $event->date->format('d') }}</span>
                                        <span class="text-[10px] uppercase leading-none mt-0.5">{{ $event->date->translatedFormat('M') }}</span>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink truncate">{{ $event->title }}</p>
                                        <p class="text-xs text-muted truncate">{{ $event->club->name }}</p>
                                    </div>

                                </a>

                            @endforeach

                        </div>

                    @endif

                </div>

                {{-- Clubs à découvrir (clubs populaires) --}}
                <div class="bg-white rounded-2xl border border-ink/10 p-5">

                    <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2 mb-4">
                        <x-lucide-trophy class="w-4 h-4 text-amber-500" />
                        Clubs à découvrir
                    </h3>

                    @if ($this->popularClubs->isEmpty())

                        <p class="text-sm text-muted text-center py-4">
                            Aucun club trouvé.
                        </p>

                    @else

                        <div class="space-y-1">

                            @foreach ($this->popularClubs as $club)

                                <a href="{{ route('clubs.show', $club) }}"
                                   wire:key="side-club-{{ $club->id }}"
                                   class="flex items-center justify-between p-2 -mx-2 rounded-lg hover:bg-pine-50 transition">

                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if ($club->logo)
                                            <img src="{{ asset('storage/' . $club->logo) }}"
                                                 class="w-8 h-8 rounded-full object-cover shrink-0">
                                        @else
                                            <span class="w-8 h-8 rounded-full bg-pine-50 flex items-center justify-center shrink-0">
                                                <x-lucide-landmark class="w-4 h-4 text-pine-600" />
                                            </span>
                                        @endif
                                        <span class="text-sm text-ink truncate">{{ $club->name }}</span>
                                    </div>

                                    <span class="text-xs text-muted shrink-0 ml-2">
                                        {{ $club->members_count }}
                                    </span>

                                </a>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>