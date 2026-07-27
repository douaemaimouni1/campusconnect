<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            🏛 Découvrir les clubs
        </h2>
    </div>

    <div class="max-w-7xl mx-auto py-8 px-6">

        
        <div class="flex flex-col md:flex-row gap-4 mb-10">

            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="🔍 Rechercher un club..."
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

        
        @if ($clubs->isEmpty())

            <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
                Aucun club ne correspond à votre recherche.
            </div>

        @else

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($clubs as $club)

                    @php
                        $status = $myMemberships[$club->id] ?? null;
                        $isPresident = $club->president_id === auth()->id();
                    @endphp

                    <div wire:key="club-{{ $club->id }}"
                         class="bg-white rounded-xl shadow overflow-hidden hover:shadow-xl transition duration-300">

                        
                        <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center">
                            @if ($club->logo)
                                <img src="{{ asset('storage/' . $club->logo) }}"
                                     class="w-20 h-20 rounded-full object-cover border-4 border-white shadow">
                            @else
                                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center text-3xl border-4 border-white shadow">
                                    🏛
                                </div>
                            @endif
                        </div>

                        <div class="p-5">

                            <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                {{ $club->category }}
                            </span>

                            <h3 class="text-xl font-bold">
                                {{ $club->name }}
                            </h3>

                            <p class="text-gray-500 text-sm mt-2 line-clamp-2">
                                {{ $club->description }}
                            </p>

                            <div class="flex items-center justify-between mt-4 text-sm text-gray-400">
                                <span>👤 {{ $club->president->name }}</span>
                                <span>{{ $club->members_count }} membre{{ $club->members_count > 1 ? 's' : '' }}</span>
                            </div>

                          
                            <div class="mt-5 flex gap-2">

                                <a href="{{ route('clubs.show', $club) }}"
                                   class="flex items-center justify-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-semibold transition">
                                    👁 Voir
                                </a>

                                @if ($isPresident)

                                    <div class="flex-1 bg-yellow-50 text-yellow-700 py-2 rounded-lg font-semibold text-center">
                                        👑 Président
                                    </div>

                                @elseif ($status === 'accepted')

                                    <button disabled
                                        class="flex-1 bg-green-50 text-green-600 py-2 rounded-lg font-semibold cursor-default">
                                        ✓ Membre
                                    </button>

                                @elseif ($status === 'pending')

                                    <button
                                        wire:click="cancelRequest({{ $club->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="cancelRequest({{ $club->id }})"
                                        class="group flex-1 bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 disabled:opacity-50 py-2 rounded-lg font-semibold transition">

                                        <span wire:loading.remove wire:target="cancelRequest({{ $club->id }})">
                                            <span class="group-hover:hidden">Envoyée</span>
                                            <span class="hidden group-hover:inline">✕ Annuler</span>
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
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white py-2 rounded-lg font-semibold transition">
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

    
            <div class="mt-10">
                {{ $clubs->links() }}
            </div>

        @endif

    </div>

</div>