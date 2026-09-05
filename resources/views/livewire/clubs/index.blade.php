<div wire:poll.visible.15s>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h2 class="font-serif font-bold text-2xl text-ink flex items-center gap-2">
                <x-lucide-landmark class="w-6 h-6 text-pine-600" />
                Découvrir les clubs
            </h2>

            <button
                wire:click="openCreateClubModal"
                class="flex items-center gap-2 bg-pine-600 hover:bg-pine-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                <x-lucide-plus class="w-4 h-4" />
                Créer un club
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        {{-- Barre de recherche + filtres --}}
        <div class="flex flex-col md:flex-row gap-4 mb-10">

            <div class="relative flex-1">
                <x-lucide-search class="w-4 h-4 text-muted-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Rechercher un club..."
                    class="w-full pl-10 rounded-xl border-muted-300 shadow-sm focus:ring-pine-500 focus:border-pine-500">
            </div>

            <select
                wire:model.live="category"
                class="rounded-xl border-muted-300 shadow-sm focus:ring-pine-500 focus:border-pine-500 md:w-64">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <button
                wire:click="$set('onlyMyPresidentClubs', {{ $onlyMyPresidentClubs ? 'false' : 'true' }})"
                class="flex items-center justify-center gap-2 px-4 py-2 rounded-xl font-semibold border transition
                       {{ $onlyMyPresidentClubs
                            ? 'bg-pine-600 border-pine-600 text-white'
                            : 'bg-white border-muted-300 text-ink-700 hover:bg-muted-50' }}">
                <x-lucide-crown class="w-4 h-4" />
                Mes clubs
            </button>

        </div>

        @if ($clubs->isEmpty())

            <div class="bg-white rounded-xl shadow p-12 text-center text-muted-400">
                Aucun club ne correspond à votre recherche.
            </div>

        @else

            @php
                $categoryStyles = [
                    'pine'       => ['badge' => 'bg-pine-50 text-pine-700'],
                    'amber'      => ['badge' => 'bg-amber-50 text-amber-700'],
                    'terracotta' => ['badge' => 'bg-terracotta-50 text-terracotta-700'],
                    'prune'      => ['badge' => 'bg-prune-50 text-prune-700'],
                    'ardoise'    => ['badge' => 'bg-ardoise-50 text-ardoise-700'],
                ];
            @endphp

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($clubs as $club)

                    @php
                        $status = $myMemberships[$club->id] ?? null;
                        $isPresident = $club->president_id === auth()->id();

                        $meta = $categoryMeta[$club->category] ?? ['icon' => 'tag', 'color' => 'ardoise'];
                        $styles = $categoryStyles[$meta['color']];

                        $words = collect(preg_split('/\s+/', trim($club->name)))->filter()->values();
                        $initials = $words->count() >= 2
                            ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
                            : mb_strtoupper(mb_substr($club->name, 0, 2));
                    @endphp

                    <div wire:key="club-{{ $club->id }}"
                         class="bg-white rounded-xl shadow overflow-hidden hover:shadow-xl transition duration-300 border-2 border-muted-300">

                        <div class="h-32 bg-pine-700 flex items-center justify-center">
                            @if ($club->logo)
                                <img src="{{ asset('storage/' . $club->logo) }}"
                                     class="w-20 h-20 rounded-full object-cover border-4 border-white shadow">
                            @else
                                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center border-4 border-white shadow">
                                    <span class="text-pine-700 font-serif font-bold text-xl">{{ $initials }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">

                            <span class="inline-flex items-center gap-1 {{ $styles['badge'] }} text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                <x-dynamic-component :component="'lucide-' . $meta['icon']" class="w-3 h-3" />
                                {{ $club->category }}
                            </span>

                            <h3 class="font-serif text-xl font-bold text-ink">
                                {{ $club->name }}
                            </h3>

                            <p class="text-muted-500 text-sm mt-2 line-clamp-2">
                                {{ $club->description }}
                            </p>

                            <div class="flex items-center justify-between mt-4 text-sm text-muted-400">
                                <span class="flex items-center gap-1">
                                    <x-lucide-user class="w-4 h-4" />
                                    {{ $club->president?->name ?? 'Aucun président' }}
                                </span>
                                <span>{{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                            </div>

                            <div class="mt-5 flex gap-2">

                                <a href="{{ route('clubs.show', $club) }}"
                                   class="flex items-center justify-center gap-1 bg-muted-100 hover:bg-muted-200 text-ink-700 py-2 px-4 rounded-lg font-semibold transition">
                                    <x-lucide-eye class="w-4 h-4" />
                                    Voir
                                </a>

                                @if ($isPresident)

                                    <div class="flex-1 bg-amber-50 text-amber-700 py-2 rounded-lg font-semibold text-center flex items-center justify-center gap-1">
                                        <x-lucide-crown class="w-4 h-4" />
                                        Président
                                    </div>

                                @elseif ($status === 'accepted')

                                    <button disabled
                                        class="flex-1 bg-pine-50 text-pine-600 py-2 rounded-lg font-semibold cursor-default flex items-center justify-center gap-1">
                                        <x-lucide-check class="w-4 h-4" />
                                        Membre
                                    </button>

                                @elseif ($status === 'pending')

                                    <button
                                        wire:click="cancelRequest({{ $club->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="cancelRequest({{ $club->id }})"
                                        class="group flex-1 bg-muted-100 hover:bg-red-50 text-muted-500 hover:text-red-600 disabled:opacity-50 py-2 rounded-lg font-semibold transition">

                                        <span wire:loading.remove wire:target="cancelRequest({{ $club->id }})">
                                            <span class="group-hover:hidden">Envoyée</span>
                                            <span class="hidden group-hover:inline-flex items-center gap-1 justify-center">
                                                <x-lucide-x class="w-4 h-4" />
                                                Annuler
                                            </span>
                                        </span>

                                        <span wire:loading wire:target="cancelRequest({{ $club->id }})">
                                            ...
                                        </span>

                                    </button>

                                @else

                                    <button
                                        wire:click="joinClub({{ $club->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="joinClub({{ $club->id }})"
                                        class="flex-1 bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
                                        <span wire:loading.remove wire:target="joinClub({{ $club->id }})">
                                            Rejoindre
                                        </span>
                                        <span wire:loading wire:target="joinClub({{ $club->id }})">
                                            ...
                                        </span>
                                    </button>

                                @endif

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

    {{-- Modale de création de club --}}
    @if ($showCreateClubModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4"
            wire:click.self="closeCreateClubModal"
            wire:key="create-club-modal-overlay">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h3 class="font-serif text-xl font-bold text-ink flex items-center gap-2">
                        <x-lucide-landmark class="w-5 h-5 text-pine-600" />
                        Créer un club
                    </h3>
                    <button wire:click="closeCreateClubModal" class="text-muted-400 hover:text-ink-700">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <livewire:clubs.club-form :embedded="true" :key="'club-form-create'" />
            </div>
        </div>
    @endif

</div>