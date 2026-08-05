<div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        {{-- ================= EN-TÊTE PROFIL ================= --}}
        <div class="bg-white rounded-2xl shadow p-8 mb-6 text-center">

            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}"
                     class="w-28 h-28 rounded-full object-cover mx-auto mb-4">
            @else
                <div class="w-28 h-28 rounded-full bg-indigo-100 flex items-center justify-center text-4xl font-semibold text-indigo-600 mx-auto mb-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>

            <p class="text-gray-500 mt-1">
                🏫 {{ $user->department ?? 'Département non renseigné' }}
            </p>

            @if ($user->bio)
                <p class="text-gray-600 mt-4 leading-relaxed max-w-lg mx-auto">
                    {{ $user->bio }}
                </p>
            @endif

        </div>

        {{-- ================= CLUBS PRÉSIDÉS ================= --}}
        @if ($presidentClubs->isNotEmpty())

            <div class="bg-white rounded-2xl shadow p-6 mb-6">

                <h3 class="text-lg font-bold mb-4">⭐ Clubs présidés</h3>

                <ul class="space-y-2">
                    @foreach ($presidentClubs as $club)
                        <li>
                            <a href="{{ route('clubs.show', $club) }}"
                               class="text-indigo-600 hover:underline font-semibold">
                                {{ $club->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

            </div>

        @endif

        {{-- ================= CLUBS MEMBRE ================= --}}
        <div class="bg-white rounded-2xl shadow p-6 mb-6">

            <h3 class="text-lg font-bold mb-4">👥 Clubs membre</h3>

            @if ($memberClubs->isEmpty())

                <p class="text-gray-400 text-sm">Aucun club rejoint pour le moment.</p>

            @else

                <ul class="space-y-2">
                    @foreach ($memberClubs as $club)
                        <li>
                            <a href="{{ route('clubs.show', $club) }}"
                               class="text-indigo-600 hover:underline font-semibold">
                                {{ $club->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

            @endif

        </div>

        {{-- ================= ÉVÉNEMENTS À VENIR ================= --}}
        <div class="bg-white rounded-2xl shadow p-6">

            <h3 class="text-lg font-bold mb-4">📅 Événements à venir</h3>

            @if ($eventRegistrations->isEmpty())

                <p class="text-gray-400 text-sm">Aucun événement à venir.</p>

            @else

                <ul class="space-y-2">
                    @foreach ($eventRegistrations as $registration)
                        <li>
                            <a href="{{ route('events.show', $registration->event) }}"
                               class="text-indigo-600 hover:underline font-semibold">
                                {{ $registration->event->title }}
                            </a>
                            <span class="text-xs text-gray-400">
                                ({{ $registration->event->club->name }})
                            </span>
                        </li>
                    @endforeach
                </ul>

            @endif

        </div>

    </div>

</div>