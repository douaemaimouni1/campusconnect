<div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        {{-- ================= EN-TÊTE PROFIL ================= --}}
        <div class="bg-white rounded-2xl shadow p-8 mb-6 text-center">

            @if ($user->avatar)
            <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}"
                
                     class="w-28 h-28 rounded-full object-cover mx-auto mb-4">
            @else
                <div class="w-28 h-28 rounded-full bg-pine-50 flex items-center justify-center text-4xl font-serif font-semibold text-pine-600 mx-auto mb-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <h1 class="text-2xl font-bold text-ink">{{ $user->name }}</h1>

            <p class="flex items-center justify-center gap-1.5 text-muted-500 mt-1">
                <x-lucide-graduation-cap class="w-4 h-4" />
                {{ $user->department ?? 'Département non renseigné' }}
            </p>

            @if ($user->bio)
                <p class="text-muted-500 mt-4 leading-relaxed max-w-lg mx-auto">
                    {{ $user->bio }}
                </p>
            @endif

        </div>

        {{-- ================= CLUBS PRÉSIDÉS ================= --}}
        @if ($presidentClubs->isNotEmpty())

            <div class="bg-white rounded-2xl shadow p-6 mb-6">

                <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2 mb-4">
                    <x-lucide-crown class="w-4 h-4 text-pine-600" />
                    Clubs présidés
                </h3>

                <ul class="space-y-2">
                    @foreach ($presidentClubs as $club)
                        <li>
                            <a href="{{ route('clubs.show', $club) }}"
                               class="text-pine-600 hover:underline font-semibold">
                                {{ $club->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

            </div>

        @endif

        {{-- ================= CLUBS MEMBRE ================= --}}
        <div class="bg-white rounded-2xl shadow p-6 mb-6">

            <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2 mb-4">
                <x-lucide-users class="w-4 h-4 text-pine-600" />
                Clubs membre
            </h3>

            @if ($memberClubs->isEmpty())

                <p class="text-muted-500 text-sm">Aucun club rejoint pour le moment.</p>

            @else

                <ul class="space-y-2">
                    @foreach ($memberClubs as $club)
                        <li>
                            <a href="{{ route('clubs.show', $club) }}"
                               class="text-pine-600 hover:underline font-semibold">
                                {{ $club->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

            @endif

        </div>

        {{-- ================= ÉVÉNEMENTS À VENIR ================= --}}
        <div class="bg-white rounded-2xl shadow p-6">

            <h3 class="font-serif text-lg font-bold text-ink flex items-center gap-2 mb-4">
                <x-lucide-calendar class="w-4 h-4 text-pine-600" />
                Événements à venir
            </h3>

            @if ($eventRegistrations->isEmpty())

                <p class="text-muted-500 text-sm">Aucun événement à venir.</p>

            @else

                <ul class="space-y-2">
                    @foreach ($eventRegistrations as $registration)
                        <li>
                            <a href="{{ route('events.show', $registration->event) }}"
                               class="text-pine-600 hover:underline font-semibold">
                                {{ $registration->event->title }}
                            </a>
                            <span class="text-xs text-muted-500">
                                ({{ $registration->event->club->name }})
                            </span>
                        </li>
                    @endforeach
                </ul>

            @endif

        </div>

    </div>

</div>